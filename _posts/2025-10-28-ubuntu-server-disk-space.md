---
layout: post
title: "Investigating rising disk space usage on an Ubuntu web server"
summary: >
  The disk usage on my tiny Hetzner web server had gone from 25% in January, to 50% in October. I figured it was time to work out what was causing it. The answers will shock you. (They won’t: it’s log files and docker images, it’s always log files and docker images).
related:
  - /post/hetzner-docker-caddy/
  - /post/daily-monitoring-emails-ubuntu/
---

Since I [set up daily logwatch emails](/post/daily-monitoring-emails-ubuntu/) on my Ubuntu web server, I’ve noticed the disk usage slowly creeping up each day – starting at 25% in January, and now at 47%, 10 months later.

Left unchecked, this would likely fill my disk in a year or two’s time. So I figured I should try to diagnose what was eating up the free space, and decide whether it could be stopped/reduced.

I started by looking at the largest directories on disk:

```sh
sudo du -h --max-depth=1 / | sort -hr | head -20
```

`/var` was shown as occupying 20G of space. So I dug into that (note `--max-depth=2` to show more levels at once):

```sh
sudo du -h --max-depth=2 /var | sort -hr | head -20
```

This showed that 16G was taken by `/var/lib/docker`, and 3.6G by `/var/log/journal`.

# Pruning Docker files

`sudo docker system df` showed a summary of Docker’s disk usage, including 23 images occupying 7.3 GB of space, of which Docker estimated 5 GB was “reclaimable”.

I ran the following commands, to prune stopped containers and unused images:

```sh
sudo docker container prune
sudo docker image prune
sudo docker builder prune
```

The `container prune` and `builder prune` reclaimed almost nothing, but `image prune` reclaimed 3.6 GB in dangling images.
I ran `docker volume ls -f "dangling=true"` to identify any unused volumes that could be deleted, but there were none.

# Pruning systemd journal log files

`sudo journalctl --disk-usage` confirmed “Archived and active journals take up 3.3G in the file system”. I wonder where the other 0.3 GB went! Anyway, regardless, there’s no way I need 10 months of daily systemd logs any more.

To prevent this build-up, I customised my journald configuration file with `sudo nano /etc/systemd/journald.conf`, uncommenting/modifying the following lines:

```conf
[Journal]
SystemMaxUse=500M
MaxRetentionSec=2week
MaxFileSec=1day
```

The `SystemMaxUse` line prevents journald log files from taking up more than 500 MB of space, while the `MaxRetentionSec` line retains only the last 2 weeks of logs. The `MaxFileSec` line isn’t required, but I figured I’d add it at the same time, to tidy up the log files – it will set journald to start a new log file each day, which will make reviewing the logs easier if I ever need to do it in the future.

I then restarted journald, to apply the changes:

```sh
sudo systemctl restart systemd-journald
```

A second run of `sudo journalctl --disk-usage` showed that the disk usage had been reduced to 52.8M . I guess, depending on your server, you might be able to tweak those `SystemMaxUse` and `MaxRetentionSec` settings up or down, to be more in sync with each other (in my case, the `2week` cut-off is kicking in way before the `500M` cut-off).

# The proof of the pudding

Next morning, my server delivered its daily sysstat email into my inbox, with a very pleasing graph of filesystem usage at the end:

{% img alt="A line graph showing disk usage over the course of a day. The line starts at almost 50%, then falls with two steep drops around 9am, remaining at 25% for the rest of the day." src="/media/sysstat-filesystem-usage-2025-10-27.png" %}

Mission accomplished!
