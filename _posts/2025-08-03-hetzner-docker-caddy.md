---
layout: post
title: "Setting up a dockerised Caddy-based webserver on Hetzner Cloud"
summary: >
  I’ll walk you through the steps for hosting multiple websites on a single VPS, with each site isolated in its own docker container for increased security, and Caddy handling SSL certificates automatically.
related:
  - /post/spf-dkim-dmarc/
  - /post/daily-monitoring-emails-ubuntu/
---

This blog—along with a number of other websites I run—had been hosted by [Dreamhost](https://www.dreamhost.com/) since 2008. But last year, when they announced they’d now have to charge VAT _on top_ of the $13/mth I was already paying them, I figured it was time to shop around for alternatives.

It wasn’t just about price either – I was also increasingly uncomfortable with having my data stored in the USA, and as my needs had progressed beyond just basic PHP hosting to building Jekyll sites and deploying with Git hooks, Dreamhost’s “not quite a VPS” basic tier became more and more awkward to work with.

I settled on [Hetzner Cloud’s CX22 vCPU](https://www.hetzner.com/cloud/) as a suitable alternative – as long as your processing or bandwidth requirements aren’t significant, they just can’t be beaten on price. With VAT and an (optional extra!) IP4 address, it costs me £4/mth – cheaper than a [Digital Ocean Small Droplet](https://www.digitalocean.com/pricing/droplets) or a [Mythic Beasts VPS 1](https://www.mythic-beasts.com/servers/virtual), but with four times the RAM and twice the storage. Wow. I even snagged a €20 voucher on the [Hetzner Community site](https://community.hetzner.com/tutorials), which effectively gave me the first four months for free.

Dreamhost also used to handle the DNS for some of my domains, so I needed to find a replacement for that too. I went with [Cloudflare](https://www.cloudflare.com/en-gb/). I used [Luar Roji](http://roji.net/)’s [Dreamhost DNS exporter](https://github.com/cyberplant/dreamhost_zone_exporter) to save me a half an hour’s work copying and pasting between the two sites.

# Setting up the VPS

The Hetzner Cloud setup wizard makes it super easy to boot and configure a new VPS. You pick a distribution (I chose Ubuntu, as I’m most familiar with that), upload an SSH public key for the `root` user, and boom, you’re ready to SSH in.

You’ll then want to do basic setup and security – see some examples [here](https://blog.akbal.dev/how-to-completely-secure-an-ubuntu-server), [here](https://tonyteaches.tech/secure-ubuntu-server/), and [here](https://getdeploying.com/guides/secure-ubuntu-server). In my case:

## Set up a non-root user account

```sh
adduser zarino
usermod -aG sudo zarino
su zarino
cd /home/zarino
mkdir .ssh
chmod 700 .ssh
```

Then I copied my SSH public key to `/home/zarino/.ssh/authorized_keys` on the remote server, with `ssh-copy-id zarino@<hetzner-ip-address>` from my Mac (because I already had [ssh-copy-id installed via Homebrew](https://formulae.brew.sh/formula/ssh-copy-id)). I guess you could copy/paste your SSH key in by hand.

## Tighten login requirements

Secure your logins by editing `/etc/ssh/sshd_config`:

1. Uncomment the `#PermitRootLogin…` line and change `prohibit-password` to `no` 
2. Uncomment the `#PasswordAuthentication…` line and change `yes` to `no` 
3. Change `UsePAM yes` to `UsePAM no` 
4. Confirm the following are set (or the default):
   - `ChallengeResponseAuthentication no` (was replaced by `KbdInteractiveAuthentication` in Ubuntu 22.04)
   - `KerberosAuthentication no`
   - `GSSAPIAuthentication no`
   - `X11Forwarding no`
   - `PermitUserEnvironment no`
   - `DebianBanner no`

Then validate the syntax of `sshd_config` with `sudo sshd -t`. Assuming it’s fine, restart SSH with `sudo systemctl restart ssh`. Then, in a new terminal on your local machine (without closing your current SSH session in the original terminal – just in case!):

1. Confirm `ssh root@<hetzner-ip-address>` is refused
2. Confirm `ssh -o PubkeyAuthentication=no zarino@<hetzner-ip-address>` is refused
3. Confirm `ssh zarino@<hetzner-ip-address>` is accepted

You can now do the rest of the setup in that new, non-root user account.

## Set up firewall

With logins secured, it’s time to set up Ubuntu’s firewall:

```sh
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

`sudo ufw status` will show you that ports 22, 80, and 443 are allowed. Everything else is denied.

## Update system packages

I also updated system packages and removed a few packages I knew I wouldn’t need (unused packages are just a potential source of vulnerabilities!), although I think in the end all of the packages had never been installed in the first place:

```sh
sudo apt update
sudo apt full-upgrade -y
sudo apt autoremove -y
sudo apt-get purge --auto-remove telnetd ftp vsftpd samba nfs-kernel-server nfs-common
```

## Set up unattended upgrades

```sh
sudo apt install unattended-upgrades
systemctl enable unattended-upgrades
systemctl start unattended-upgrades
```

Then confim `/etc/apt/apt.conf.d/20auto-upgrades` contains:

- `APT::Periodic::Update-Package-Lists "1";`
- `APT::Periodic::Unattended-Upgrade "1";`
- `APT::Periodic::AutocleanInterval "7";`

And perform a dry run with `sudo unattended-upgrades --dry-run --debug`.

You could also enable email notifications about security updates, by adding the following two lines to `/etc/apt/apt.conf.d/50unattended-upgrades`:

- `Unattended-Upgrade::Mail "your@email.com";`
- `Unattended-Upgrade::MailOnlyOnError "true";`

## Set up Docker

Last time I set up a webserver (an EC2 instance in 2020) I configured a LAMP stack from scratch. It was horrendous. This time, I decided to use Docker as much as possible – both to compartmentalise projects and pieces of software, and also to create a reproducable build that could be torn down and recreated on another server if I ever needed to.

Install Docker by [following the instructions here](https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository) and then follow the [Linux post-install steps](https://docs.docker.com/engine/install/linux-postinstall/), namely:

1. `sudo groupadd docker` (already existed)
2. `sudo usermod -aG docker $USER`
3. Log out and back in
4. Confirm your user can run `docker` commands without `sudo`, eg: `docker run hello-world`
5. `sudo systemctl enable docker.service`
6. `sudo systemctl enable containerd.service`

I also [enabled the “local” logging driver](https://docs.docker.com/engine/logging/drivers/local/) with `"log-driver": "local"` in Docker’s `daemon.json`.

And finally, because I’m a lazy typist, I enabled bash completions for docker commands with `docker completion bash > /etc/bash_completion.d/docker-compose.sh` (run in a `root` shell).

## Set up sendmail, logwatch, and sysstat

These aren’t necessary, but I wanted some form of regular monitoring of my server, via a daily/weekly email.

Setting these three things up is a little beyond the scope of this post – but maybe I’ll write another one about them, specifically, later!

# Set up Caddy via docker-compose

I created a directory at `/opt/personal-hosting` to store my docker provisioning stuff, and also initialised that as a Git repo, so that I could track changes, and pull it to my local machine, to make editing easier.

I also created a directory at `/srv` to store the source files for the simpler domains I wanted to host (eg: Jekyll’s static output for this blog, and the PHP source files for my parents’ website).

In all, the files and directories of interest were:

```
/
├ opt/
│ └ personal-hosting/
│   ├ etc-caddy/
│   │ ├ access_log.conf
│   │ ├ Caddyfile
│   │ └ security_headers.conf
│   ├ script/
│   │ └ caddy-reload
│   └ docker-compose.yml
├ srv/
│ ├ zarino.co.uk/
│ │ └ …
│ └ zappia.co.uk/
│   └ …
└ var/
  └ log/
    └ caddy/
```

## docker-compose.yml

My initial `docker-compose.yml` looked like this:

```yaml
services:
  caddy:
    container_name: caddy
    hostname: caddy
    image: caddy:latest
    restart: unless-stopped
    depends_on:
      - php-fpm
    cap_add:
      - NET_ADMIN
    ports:
      - "80:80"
      - "443:443"
      - "443:443/udp"
    networks:
      - caddynet
    volumes:
      # Share directory containing Caddyfile, rather than Caddyfile itself,
      # because of https://github.com/caddyserver/caddy-docker/issues/364
      - ./etc-caddy:/etc/caddy:ro
      # Share vhost directories, to serve static files from.
      - /srv:/srv
      # Share /var/log/caddy (created with `mkdir` and chmodded to be writeable).
      - /var/log/caddy:/var/log/caddy
      # Persist Caddy data and config across container restarts.
      - caddy_data:/data
      - caddy_config:/config

  php-fpm:
    container_name: php-fpm
    hostname: php-fpm
    image: php:fpm
    restart: unless-stopped
    networks:
      - caddynet
    volumes:
      - /srv:/var/www/html

networks:
  caddynet:
    attachable: true
    driver: bridge

volumes:
  caddy_data:
  caddy_config:
```

With this, I am able to start and stop the entire set of containers with:

```sh
docker compose up -d
docker compose down
```

Or start/stop an individual container with, eg:

```sh
docker compose up -d caddy
docker compose down caddy
```

Most professional docker images are set to send their logging output to stdout, so you can read that output with, eg:

```sh
docker compose logs -f --tail 20 caddy
```

Commands that I run often, I tend to put into their own file, eg: `script/caddy-reload`, which I run every time I’ve edited my Caddyfile:

```sh
docker compose exec caddy caddy reload --config /etc/caddy/Caddyfile
```

Of course I can also just SSH into the container for a service, if I need to do anything more involved, eg:

```sh
docker compose exec caddy bash
```

## Caddy config

My `Caddyfile` looks like this:

```conf
{
	log default {
		output stdout
		format json
	}
}

www.zarino.co.uk {
	redir https://zarino.co.uk{uri}
}

zarino.co.uk {
	import access_log.conf "zarino.co.uk"
	import security_headers.conf
	encode zstd gzip

	root * /srv/zarino.co.uk

	file_server

	handle_errors {
		rewrite * /{err.status_code}/
		file_server
	}
}

www.zappia.co.uk {
	redir https://zappia.co.uk{uri}
}

zappia.co.uk {
	import access_log.conf "zappia.co.uk"
	import security_headers.conf
	encode zstd gzip

	root * /srv/zappia.co.uk

	php_fastcgi php-fpm:9000 {
		# Tell php-fpm where to find the PHP files _inside_ the Docker container.
		# (Our docker-compose.yml maps /srv on the host to /var/www/html inside the container.)
		root /var/www/html/zappia.co.uk
	}
	file_server
}
```

To save repeating the same config options again and again for each domain Caddy is hosting, I broke those out into their own partial files I could then import. Namely, `access_log.conf`:

```conf
log {
	output file /var/log/caddy/{args[0]}.log
}
```

And `security_headers.conf`:

```conf
header /* {
	Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
	X-Content-Type-Options nosniff
	X-Frame-Options sameorigin
	Referrer-Policy strict-origin-when-cross-origin
	Content-Security-Policy "default-src https:; font-src https: data:; img-src https: data: 'self' about:; script-src 'unsafe-inline' https: data:; style-src 'unsafe-inline' https:; connect-src https: data: 'self'"
}
```

Caddy handles the registration and management of SSL certificates for every domain, automatically. Which is, frankly, witchcraft.

Other things to note:

1. The Caddyfile format is a breath of fresh air compared to nginx configs or (god forbid) Apache configs, but it still has its own quirks. In particular, note that [directives inside your site blocks are re-ordered by Caddy](#) before being applied, which can result in unexpected behaviour. I try to ensure the order of directives inside my blocks roughly matches [the order that Caddy expects them](#), so there’s less opportunity for surprise.
2. The `zarino.co.uk` site is simply hosting a bunch of static HTML, CSS, and image files, pre-compiled by Jekyll. So all it needs is a `file_server` block to handle that.
3. The `zappia.co.uk` site, in comparison, is a PHP site. So it needs both the `php_fastcgi` block, and the `file_server` directive for any non-PHP static files.
4. The `php_fastcgi` block is communicating with the `php-fpm` container, over port `9000`. It’s really nice being able to refer to services from my `docker-compose.yml` file, by their hostname, in this `Caddyfile` – especially when you set up containers for each WordPress site you’re hosting, for example.
