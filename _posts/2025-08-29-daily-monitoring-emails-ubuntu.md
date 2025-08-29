---
layout: post
title: "Setting up daily monitoring emails on your Ubuntu server"
summary: >
  Proper sysadmins use fancy tools like Grafana to monitor the health of their servers. But if all you need is some basic stats in a daily (or weekly) email, here’s how to set it up, with logwatch and sysstat.
related:
  - /post/hetzner-docker-caddy/
  - /post/spf-dkim-dmarc/
---

If you followed my guide on [Setting up a dockerised Caddy-based webserver on Hetzner Cloud](/post/hetzner-docker-caddy/) you may now be wondering how to keep an eye on your server’s performance while you’re busy living the rest of your life. Maybe you want to check that the disk isn’t filling up. Or maybe—as I found a few weekends ago—you’d like to know when the [kinsing/kdevtmpfsi malware](https://gist.github.com/yoyosan/5f88c1a023f006f952d7378bdc7bcf01) has infected one of your Docker containers and is consuming 200% CPU. (Grrrr.)

There are lots of very advanced ways that professionals would set up live monitoring for high value servers. But for the hobbyist, a daily email strikes a good balance between knowing that everything’s ticking over ok and not investing lots in monitoring what is essentially a toy machine.

When I [migrated my web hosting from Dreamhost to Hetzner Cloud](/post/hetzner-docker-caddy/), I set up two daily emails to give me glanceable stats over my morning coffee – [logwatch](https://linux.die.net/man/8/logwatch) and [sysstat](https://sysstat.github.io/). Here’s how:

# Monitoring system logs with logwatch

It’s easiest to switch into the root user for all of these commands:

```sh
sudo sh
```

Install [logwatch](https://linux.die.net/man/8/logwatch) and create a cache directory for it ([as recommended by the Ubuntu Server docs](https://documentation.ubuntu.com/server/how-to/observability/install-logwatch/)):

```sh
apt install logwatch
mkdir /var/cache/logwatch
```

Note: I’m writing this in August 2025, and running the latest Ubuntu LTS (24.04 Noble), so `apt`, unfortunately, installs quite an old version of logwatch (7.7, from July 2022). Logwatch 7.12 is already packaged for Ubuntu 24.10 Oracular and 25.04 Plucky, and I expect it’ll be the default for April 2026’s LTS release. So if you’re reading this in 2026 or beyond, and have a newer version of logwatch installed, you may need to tweak the following instructions slightly.

Logwatch will read its configuration from your custom config directory at `/etc/logwatch/conf/`, falling back to defaults in `/usr/share/logwatch/default.conf/` if any config is not found. For any config files we want to edit, we’ll copy them out of `default.conf` and into our custom config directory, so that our edits don’t get overwritten when logwatch is upgraded.

Copy the main logwatch config file into our custom config directory:

```sh
cp /usr/share/logwatch/default.conf/logwatch.conf /etc/logwatch/conf/logwatch.conf
```

And uncomment/overwrite these settings in the new `/etc/logwatch/conf/logwatch.conf` (swapping the placeholders with real email addresses):

```conf
Output = mail
Format = html

MailTo = me@example.com
MailFrom = logwatch@example.com

Detail = Low # this is the default
Service = All # this is the default
```

We’ll also want to override how logwatch reports SSH activity, to avoid it including spam domain names in the emails it sends (which can otherwise trigger Spam filters). Copy the logwatch ssh service config into our custom config directory:

```sh
cp /usr/share/logwatch/default.conf/services/sshd.conf /etc/logwatch/conf/services/
```

And overwrite this setting in `/etc/logwatch/conf/services/sshd.conf`:

```conf
$sshd_ip_lookup = No
```

If you want to switch from the default daily reports to weekly reports, replace `Range = yesterday` with `Range = between -7 and -1 days` in your custom `/etc/logwatch/conf/logwatch.conf` file, and move the cronjob to weekly:

```sh
mv /etc/cron.daily/00logwatch /etc/cron.weekly/
```

There is more “documentation” on how to customise logwatch in the text file at `/usr/share/doc/logwatch/HOWTO-Customize-LogWatch.gz` but be warned, it’s pretty dense!

You can test it all works by running `logwatch` or `logwatch --range today` at the command line (or `logwatch --range today --output stdout` if you want it to just print the HTML rather than emailing it to you).

Remember: You’ll need to have email sending set up on your server, or logwatch will just be piping its messages into the void. Here’s [my guide on setting up email sending, SPF, DKIM, and DMARC on your Ubuntu server](/post/spf-dkim-dmarc/).

If you want to exclude overlay filesystems from the Disk Space report (they have very long names which break the email layout on narrow screens, and also, they’re just duplicates of the main filesystem line) then you can filter them out of the output by creating a file at `/etc/logwatch/conf/ignore.conf` and putting this line into it:

```
^overlay\s
```

This will ignore any log output lines (in any report, not just the Disk Space report) that start with the word "overlay", followed by a space character.

## Bonus: Improve the styling of logwatch emails

I found the styling of the logwatch emails to be pretty hideous, and also wasteful of screen space, so I copied the HTML files from `/usr/share/logwatch/default.conf/html` to `/etc/logwatch/conf/html/` and edited the copies.

Here’s what’s in my `/etc/logwatch/conf/html/header.html`:

```html
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
<title>Logwatch $Version ($VDate)</title>
<meta name="generator" content="Logwatch $Version ($VDate)">
<style type="text/css">
  body {
    width: 90%;
    margin: 0 5%;
    background: #FFFFFF;
    font-family: system-ui, sans-serif;
  }

  table {
    border-spacing: 0;
    border-collapse: collapse;
    border: none;
    margin: 2em 0;
  }

  th, td {
    padding: 0.5em;
    text-align: left;
    background: #fff;
    border: 1px solid #ccc;
  }

  tr:first-child th {
    background: #eee;
  }

  td {
    font-family: monospace;
  }

  table h2 {
    margin-bottom: 0;
    font-size: 1.25em;
  }

  ul {
    padding: 2em 0 0 0;
    margin: 0;
  }

  li {
    list-style: none;
    display: inline;
    margin-right: 1em;
  }

  ul > a, .return_link {
    display: none;
  }

  .copyright {
    margin-top: 2em;
    color: #666;
    font-size: 0.75em;
  }
</style>
</head>
<body>
<!-- End header.html -->
```

And my `/etc/logwatch/conf/html/footer.html`:

```html
<!-- Start footer.html -->
<div class="copyright">
<p>Logwatch &copy; Copyright 2002-2021 Kirk Bauer</p>
<p>
Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:
</p>
<p>
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
</p>
<p>
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
</p></div>
</body></html>
```

In a nutshell, these new templates:
- Remove `<hr>` elements from header and footer
- Hide “return to top” links
- Collapse the table of contents into a single row
- Simplify the table styling
- Fix a quoting bug on the copyright class attribute in `footer.html`

# Monitoring resource use with sysstat

[Sysstat](https://sysstat.github.io/) is a fairly well respected collection of performance monitoring tools for Linux servers. The only annoying thing is that, once sysstat has compiled data on your system’s performance, it doesn’t then have a built-in way of visualising and emailing you the output.

Handily, [someone has written a wrapper](https://github.com/desbma/sysstat_mail_report) which uses sysstat to collect the data, and then generates SVG or PNG graphs out of it, and emails those graphs to you via `sendmail` (or a compatible mail transfer agent, like Postfix).

Here’s how I got it set up to email me graphs every morning.

Again it’s easiest to do all of the following steps inside a root shell:

```sh
sudo su
```

Install the required packages for [sysstat_mail_report](https://github.com/desbma/sysstat_mail_report) (I found I already had `python3` and `sendmail-bin` installed):

```sh
apt update
apt install sysstat gnuplot-nox
```

If you want graphs for filesystem usage and socket/tcp connections, you need to tell sysstat to collect the data. Edit `/etc/sysstat/sysstat` and add `XDISK,SNMP,IPV6` to the `SADC_OPTIONS` variable, like so:

```sh
SADC_OPTIONS="-S DISK,XDISK,SNMP,IPV6"
```

Then enable sysstat data collection by setting `ENABLED="true"` in `/etc/default/sysstat`.

Finally, set sysstat to run on system boot, and also start it running now:

```sh
systemctl enable sysstat
systemctl start sysstat
```

Git clone the [sysstat_mail_report repo](https://github.com/desbma/sysstat_mail_report) somewhere on your machine. I put stuff like this into `/opt`, so:

```sh
git clone https://github.com/desbma/sysstat_mail_report.git /opt/sysstat_mail_report
```

You can test out the script by running (swapping the placeholders with real email addresses):

```sh
cd /opt/sysstat_mail_report
sysstat_report.py daily 'Sysstat <sysstat@example.com>' 'me@example.com'
```

Once you’re happy, install the systemd unit files:

```sh
cd /opt/sysstat_mail_report
./install-systemd.sh
```

And then edit `/etc/conf.d/sysstat-report` to update the default config with at the very least real email addresses.

Finally, enable the daily systemd unit:

```sh
systemctl enable --now sysstat-report@daily.timer
```

I found I had to disable sysstat’s recommended [AppArmor](https://en.wikipedia.org/wiki/AppArmor) hardening, because otherwise I’d get errors (in `journalctl -xeu sysstat-report@daily.service`) that sysstat `can not chdir(/var/spool/mqueue-client/): Permission denied`. If you find `./sysstat_report.py` works fine from your command line, but the scheduled send via systemd never arrives, then this might be the cause. You’ll need to edit `/etc/systemd/system/sysstat-report@.service` and comment out the four lines as directed by a comment there.
