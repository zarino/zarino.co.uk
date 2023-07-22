---
layout: post
title: "Running Transmission and ZeroTier One via Docker on Synology DSM 7"
summary: >
  In which Zarino finally gets round to upgrading the operating system on his Synology NAS, and, as a result, has to work out how to install the Docker versions of two of the pieces of software he runs on it.
---

Some of the most popular posts on my blog are the ones describing how I set up my Synology Diskstation many years ago. Things like [setting up Time Machine](/post/time-machine-ds214se-nas/), [writing to HFS+ drives](/post/write-hfs-synology-nas/), and [enabling SSH access and installing third-party software through ipkg](/post/ds214se-under-the-hood/).

A lot has changed since then! [Docker](https://www.docker.com/) has become the de-facto way to run third-party software on Diskstations, and my setup (automatically migrated from a DS214se to a DS218+, and [from DSM version 4, to 5, to 6](/post/custom-software-synology-dsm-update/)) was getting [increasingly creaky and fragile](/post/synology-package-start-stop/).

After a frantic few weeks, I finally found myself with a free weekend and figured, hey, what better way to spend it than finally upgrading my DS218+ to DSM 7.1, and replacing any old, unsupported ipkg apps with new Docker-based alternatives. This post documents most of the steps I took, in case it’s useful to anyone else.

(Happily, I’ve been using Docker for a few years at [mySociety](https://www.mysociety.org), so lots of it is familiar to me. I’ll assume any readers of this post are similarly familiar. Good luck!)

## Installing and setting up Docker

If your Diskstation supports Docker, it will be listed under “All Packages” in the [Synology Package Center](https://kb.synology.com/en-global/DSM/help/DSM/PkgManApp/PackageCenter_desc?version=7) (accessible via [your Diskstation’s web interface](https://kb.synology.com/en-global/DSM/help/DSM/MainMenu/get_started?version=7)). If your Diskstation doesn’t support Docker, then you’re ~~<abbr title="Shit Out of Luck">S.O.L.</abbr>~~ going to have to try [installing it via an unofficial package](https://tylermade.net/2017/09/28/how-to-install-docker-on-an-unsupported-synology-nas/).

Once you’ve installed it, [a new “Docker” app](https://kb.synology.com/en-uk/DSM/help/Docker/docker_desc?version=7) will be available in the DSM Main Menu, and the `docker`, `docker-compose`, etc commands will be available to command-line users logged in via SSH (if you’ve [enabled SSH access to your Diskstation](https://kb.synology.com/en-uk/DSM/tutorial/How_to_log_in_to_DSM_with_key_pairs_as_admin_or_root_permission_via_SSH_on_computers), which you are definitely going to want to do).

The Docker package installs Docker as `root`. You can verify this by SSHing into the Diskstation with your usual user account, and attempting to run [a simple docker container](https://hub.docker.com/_/hello-world/):

```
$ docker run hello-world
docker: Got permission denied while trying to connect to the Docker daemon socket at unix:///var/run/docker.sock: Post "http://%2Fvar%2Frun%2Fdocker.sock/v1.24/containers/create": dial unix /var/run/docker.sock: connect: permission denied.
See 'docker run --help'.
```

Indeed, if you look at `/var/run/docker.sock`, you’ll see it’s owned by `root`:

```
$ ls -hila /var/run/docker.sock
222631 srw-rw---- 1 root root 0 Apr  1 12:57 /var/run/docker.sock
```

I didn’t want to have to use `sudo` for all my docker commands, so I created a new `docker` user group, and added my normal user account (`zarino`, below) to it:

```
$ sudo synogroup --add docker
$ sudo synogroup --member docker zarino
```

And then made the `docker.sock` file group-owned by that new `docker` group:

```
$ sudo chown root:docker /var/run/docker.sock
```

Remember to end your SSH session and start a new one, for the change to your user’s group to take effect.

If the command line frightens you, you could probably use the DSM web UI to [set up the group and membership](https://kb.synology.com/en-global/DSM/help/DSM/AdminCenter/file_group_create?version=7), and then [change the ownership of the file](https://kb.synology.com/en-us/DSM/help/FileStation/privilege?version=7). But honestly, who has the time for all that pointing and clicking.

## Installing and setting up Transmission

[linuxserver/transmission](https://registry.hub.docker.com/r/linuxserver/transmission/) is the most popular Docker container for Transmission. You’ll see, from the documentation, that it requires three directories that it will mount inside the container, as shared volumes: `/config`, `/downloads`, and `/watch`. 

I already have a Downloads folder at `/volume1/files/Downloads`, so I’ll use that. But I created the the other two directories like so:

```
$ mkdir -p /volume1/docker/transmission/config
$ mkdir -p /volume1/docker/transmission/watch
```

(Note from the future: I found downloads would fail with a “Permission denied” error unless the owner of the `/downloads` folder inside the container had execute permissions. Since I’d already verified—with `stat /volume1/files/Downloads`—that the folder was owned by my `zarino` user (UID `1027`, GID `100`), I ran `chmod u+x /volume1/files/Downloads` to ensure that the owner had execute rights.)

With the folders created, I took [their recommended docker-compose file](https://registry.hub.docker.com/r/linuxserver/transmission/), and put it into `/volume1/docker/transmission/docker-compose.yml`, modifying the settings as required. I’ll explain some of them below.

(Note: If you need a command-line file editor program other than `vi` or `vim`, the third-party [SynoCli File Tools package](https://synocommunity.com/package/synocli-file), available under the [“Community” tab in Synology Package Center](https://kb.synology.com/en-us/DSM/tutorial/How_to_install_applications_with_Package_Center#x_anchor_id5), includes easier editors like `nano`.)

`TZ` is the timezone you want the container to use. You can check your current timezone with `ls -l /etc/localtime`, which should reflect whatever timezone is set in the [‘Regional Options’ Control Panel](https://kb.synology.com/en-us/DSM/help/DSM/AdminCenter/system_time?version=7) in the DSM web interface.

The `PUID` and `GUID` values are the user ID and group ID of the user you want Transmission to run as – and note, by extension, you’ll also want to make sure this user has access to any of the directories you’re sharing as volumes in the `docker-compose.yml` file. You can get the IDs for the current user by running the `id` command at the command line, and pulling out the `uid` and `gid` values, respectively:

```
$ id
uid=1027(zarino) gid=100(users) groups=100(users),25(smmsp),101(administrators),65537(docker)
```

The `USER` and `PASS` variables are the username and password that will be used for the HTTP Basic Authentication protecting the Transmission web interface behind a login prompt. I think the old `ipkg` version of Transmission used to use the username and password of your Synology user account here, but now I guess you could pick anything you like.

In the end, my `/volume1/docker/transmission/docker-compose.yml` file looked like this:

```
version: "3.9"

services:
  transmission:
    image: lscr.io/linuxserver/transmission:latest
    container_name: transmission
    environment:
      - PUID=1027
      - PGID=100
      - TZ=Europe/London
      - USER=examplechangeme
      - PASS=examplechangeme
    volumes:
      - /volume1/docker/transmission/config:/config
      - /volume1/files/Downloads:/downloads
      - /volume1/docker/transmission/watch:/watch
    ports:
      - 9091:9091
      - 51413:51413
      - 51413:51413/udp
    restart: unless-stopped
```

I could now install and start the container (in [detached mode](https://docs.docker.com/engine/reference/commandline/compose_up/)) with:

```
$ cd /volume1/docker/transmission
$ docker-compose up --detach
```

Once the container is running, the Transmission web interface will be accessible at `http://<your-diskstation-ip>:9091/transmission/web/`

Finally, you’ll want to [create a Triggered Task](https://kb.synology.com/en-global/DSM/help/DSM/AdminCenter/system_taskscheduler?version=7), to start the Transmission container automatically when your Diskstation reboots. I did this by selecting “Create > Triggered Task > User-defined script” in the [Task Scheduler control panel](https://kb.synology.com/en-global/DSM/help/DSM/AdminCenter/system_taskscheduler?version=7), and then creating a script with the following settings:

<div class="table-responsive">
<table class="table" style="font-size: 0.875em;">
<tr>
<th style="padding-left: 0;">Task name</th>
<td style="padding-right: 0;">Start Transmission Docker container</td>
</tr>
<tr>
<th style="padding-left: 0;">User</th>
<td style="padding-right: 0;">(you’ll want to pick your user account here)</td>
</tr>
<tr>
<th style="padding-left: 0;">Event</th>
<td style="padding-right: 0;">Boot-up</td>
</tr>
<tr>
<th style="padding-left: 0;">Pre-task</th>
<td style="padding-right: 0;">(none)</td>
</tr>
<tr>
<th style="padding-left: 0;">Enabled</th>
<td style="padding-right: 0;">(checked)</td>
</tr>
<tr>
<th style="padding-left: 0;">Notification</th>
<td style="padding-right: 0;">(none)</td>
</tr>
<tr>
<th style="padding-left: 0; white-space: nowrap;">User-defined script</th>
<td style="padding-right: 0;"><pre style="white-space: break-spaces;">cd /volume1/docker/transmission && docker-compose up --detach</pre></td>
</tr>
</table>
</div>

## Installing and setting up ZeroTier One

[ZeroTier One](https://www.zerotier.com/) is a system that puts all of your devices onto a virtual network, a bit like a VPN, meaning you can access one device from another, fairly securely, over the public internet, no matter where the devices are. I have it set up so that I can access my Diskstation and gaming PC from my Mac, even when I’m not at home.

Because ZeroTier is like a VPN, it requires what Linux geekily calls a “persistent [TUN/TAP device](https://en.wikipedia.org/wiki/TUN/TAP)” in order to hijack network requests on the machine. Your Diskstation doesn’t come with a TUN/TAP device out of the box, so you’ll need to install one, before attempting to run the ZeroTier Docker container.

ZeroTier provides [instructions for setting up a TUN device](https://docs.zerotier.com/devices/synology/#create-a-persistent-tun). By the end of that, you’ll have a script at `/usr/local/etc/rc.d/tun.sh` that installs the tun kernel module, and after running it once, you’ll have a TUN device available at `/dev/net/tun`. Woop!

On to the Docker container! I diverge a little from the recommended `/var/lib/zerotier-one` file location in [ZeroTier’s installation guide](https://docs.zerotier.com/devices/synology/#create-a-persistent-tun), because, as you will have seen above, I’m storing my docker-related files in `/volume1/docker` instead, like so:

```
$ mkdir -p /volume1/docker/zerotier-one/data
```

The ZeroTier guide doesn’t include an example `docker-compose.yml` file, but you can create one, inspired by their example docker run command. Mine, at `/volume1/docker/zerotier-one/docker-compose.yml`, ended up looking like this:

```
version: "3.9"

services:
  zerotier:
    image: zerotier/zerotier-synology:latest
    container_name: zerotier-one
    devices:
      - /dev/net/tun
    network_mode: host
    volumes:
      - /volume1/docker/zerotier-one/data:/var/lib/zerotier-one
    cap_add:
      - NET_ADMIN
      - SYS_ADMIN
    restart: unless-stopped
```

Then, just like with Transmission, you can start the container in detached mode:

```
$ cd /volume1/docker/zerotier-one
$ docker-compose up --detach
```

While I was at it, I also created a wrapper script, to make issuing commands into the container a bit easier. I created a new file at `/volume1/docker/zerotier-one/zerotier-cli`, filled it with the following text, and made it executable:

```
#!/bin/sh

docker-compose exec zerotier zerotier-cli "$@"
```

With that script in place, getting the current network status, and joining a network, is easy:

```
$ cd /volume1/docker/zerotier-one
$ ./zerotier-cli status
200 info cefed4ab93 1.10.6 ONLINE
$ ./zerotier-cli join e5cd7a9e1cae134f
200 join OK
```

After authorising the Diskstation in the [ZeroTier One web panel](https://my.zerotier.com/), I could list the network details:

```
$ ./zerotier-cli listnetworks
```

Finally, as with Transmission (above), I needed to [create a Triggered Task](https://kb.synology.com/en-global/DSM/help/DSM/AdminCenter/system_taskscheduler?version=7), to start the docker container automatically when my Diskstation reboots. The details were very similar to before:

<div class="table-responsive">
<table class="table" style="font-size: 0.875em;">
<tr>
<th style="padding-left: 0;">Task name</th>
<td style="padding-right: 0;">Start ZeroTier One Docker container</td>
</tr>
<tr>
<th style="padding-left: 0;">User</th>
<td style="padding-right: 0;">(you’ll want to pick your user account here)</td>
</tr>
<tr>
<th style="padding-left: 0;">Event</th>
<td style="padding-right: 0;">Boot-up</td>
</tr>
<tr>
<th style="padding-left: 0;">Pre-task</th>
<td style="padding-right: 0;">(none)</td>
</tr>
<tr>
<th style="padding-left: 0;">Enabled</th>
<td style="padding-right: 0;">(checked)</td>
</tr>
<tr>
<th style="padding-left: 0;">Notification</th>
<td style="padding-right: 0;">(none)</td>
</tr>
<tr>
<th style="padding-left: 0; white-space: nowrap;">User-defined script</th>
<td style="padding-right: 0;"><pre style="white-space: break-spaces;">cd /volume1/docker/zerotier-one && docker-compose up --detach</pre></td>
</tr>
</table>
</div>

## Remember backups!

Finally, remember to add your `/volume1/docker` directory to your backup software’s list. I use Synology’s [HyperBackup](https://www.synology.com/en-uk/dsm/feature/hyper_backup) for this, so it was fairly easy to log into the admin interface, and add a second backup task for `/volume1/docker`.
