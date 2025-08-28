---
layout: post
title: "Starting or stopping Synology packages from the command line, or automatically on boot and shutdown"
summary: >
  Sometimes a package will stubbornly refuse to start when your Snology NAS boots up. Or sometimes you just want to run a custom script on a schedule without delving into cron. Here’s how.
related:
  - /post/ds214se-under-the-hood/
  - /post/transmission-zerotier-one-docker-synology-dsm-7/
---

A hyper specific post today – but handy if, like me, you’ve just wasted a whole evening trying to work out how to get a Synology “package” (ie: app, installed via the built-in “Package Center”) to run when the device boots up.

It all started when I noticed that my DS218+ was no longer on my ZeroTier network. [ZeroTier](https://www.zerotier.com/) is sort of like a cross between a network router and a VPN – once the ZeroTier software is running on your devices (Macs, PCs, Synology NASes, whatever) those devices will all be accessible to each other, no matter where in the world they are.

It’s magic, but it only works if the ZeroTier client is running on each device. A while ago, I noticed the ZeroTier package wasn’t starting up when my DS218+ rebooted, which meant the device had dropped off my ZeroTier network.

I could restart the ZeroTier package by logging into my DiskStation’s web interface ([find.synology.com](https://find.synology.com)), opening up the Package Center “app”, and hitting the “Run” button next to ZeroTier. But that was only a temporary fix. Really I needed a way to stop this happening again.

# Managing Synology packages from the command line

It turns out, if you’ve [set up SSH access to your DiskStation](/post/ds214se-under-the-hood), then there’s a `synopkg` command for interacting with Package Center “apps” from the command line.

You can find out whether a package is running with `synopkg is_onoff`, eg:

```sh
/usr/syno/bin/synopkg is_onoff zerotier
```

And you can start or stop a package with `start` and `stop`,[^1] eg:

```sh
/usr/syno/bin/synopkg start zerotier
/usr/syno/bin/synopkg stop zerotier
```

[^1]: From what I can tell, this is just a convenience wrapper around each package’s existing start/stop script at `/var/packages/<package>/scripts/start-stop-status`. But I guess it’s nice not having to worry about where the package is physically located, and running `synopkg start` feels like a closer equivalent to hitting the “Run” button in package Center, than running some start/stop script from inside the package directory itself.

If you can’t guess what your package is called, you can find it in the list of all packages:

```sh
/usr/syno/bin/synopkg list
```

Which will return lines like:

> zerotier-1.1.1: An Ethernet switch for Earth. Create flat virtual networks of almost unlimited size.

The package name is everything before the dash and the version number.

# Starting a package when the DiskStation boots up

You _could_ take the `/usr/syno/bin/synopkg start zerotier` command and chuck it into a script in the `/usr/local/etc/rc.d/` directory, so it’ll be run, as root, at startup and shutdown. If you want to do this, you’ll need to follow a few conventions:

1. The script should end with a `.sh` extension and have permissions `755`.
1. It should ideally accept `start` or `stop` as a parameter, because the OS effectively runs `yourscript.sh start` when the system boots, and `yourscript.sh stop` when the system shuts down.
1. And if you want your script to run in a particular order during startup, you’ll want its name to begin with a capital “S” followed by two numbers, which define the order relative to other S-named scripts in that directory, eg: `/usr/local/etc/rc.d/S99yourscript.sh` would run right at the end.

So if all that fiddling floats your boat, then go ahead.

But you bought a Synology device specifically so you _didn’t_ have to go faffing around in unixland, right? Turns out that since DSM 6.0, there’s been a task scheduler built into the DiskStation web admin UI, and it’s called… Task Scheduler.

You can find Task Scheduler in the “System” section of the DSM “Control Panel” app:

{% img src="/media/dsm-task-scheduler.png" alt="Task Scheduler control panel in Synology DiskStation Manager" width="1455" height="989" %}

In my case, I wanted to run a command at boot, so I picked “Create” > “Triggered Task” > “User defined script”. The default trigger is Boot. So then all I needed to do was paste my command into the “Run command” text box:

```sh
/usr/syno/bin/synopkg start zerotier
```

Tick the checkbox to enable the task, and click “OK”. Job done! Now the command will be run when the DiskStation starts up.
