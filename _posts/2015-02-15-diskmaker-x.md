---
layout: post
title: Is Diskmaker X taking forever to create your bootable OS X drive?
summary: >
  How to create an OS X installer using Diskmaker X, and what to do when it gives you a cryptic error: “command not found.”
related:
  - /post/time-machine-ds214se-nas/
  - /post/synology-rsync-backup/
---

My first Mac was a 2001 “Dual USB” iBook G3. Back then, Macs came with installer disks (*CDs* in the case of the iBook G3) and new releases of OS X would be sold, again as physical disks, for £79.

Time moves on though, and years ago Macs stopped coming with physical installer media. Call me old fashioned, but something about that scares me a bit.

I’ve previously talked about the importance of [backing up your shit](/post/backup-your-shit), and even shared some tips for [setting up Time Machine on a Synology NAS](/post/time-machine-ds214se-nas), and [selectively backing up to a USB drive with `rsync`](/post/synology-rsync-backup).

Having your own *physical* installer media is just the next step in making sure, no matter what happens, you can get your Mac set up immediately, after disaster strikes.

I already have two USB drive installers, for OS X 10.7.2 and 10.8.4. But this weekend, I decided to create one for OS X 10.9 (Mavericks), and I hit a problem.

There didn’t seem to be much help out there on the interwebs, so here’s hoping Google will find this page next time someone in my position wonders why their OS X installer is taking ages to start.

## “Command not found”

I was using [Diskmaker X](http://diskmakerx.com) to create a bootable drive from an OS X Mavericks installer I’d downloaded from the Mac App Store months ago.

And whenever I ran it, Diskmaker X would hang on the following screen:

{% img "Diskmaker X error" "/media/diskmaker-x-error-obscured.jpg" %}

It turns out, Diskmaker X was trying to show me an error message, but the super long directory path was hiding it. Here’s how it would have looked if I’d put the installer in the `/Applications` directory:

{% img "Diskmaker X “command not found”" "/media/diskmaker-x-error.jpg" %}

The error says:

```
sudo: /Applications/Install OS X Mavericks.app/Contents/Resources/createinstallmedia: command not found
```

Odd. I reverted to running `createinstallmedia` myself, from the Terminal, to see whether Diskmaker X was the culprit:

```sh
sudo /Applications/Install\ OS\ X\ Mavericks.app/Contents/Resources/createinstallmedia --volume /Volumes/Untitled --applicationpath /Applications/Install\ OS\ X\ Mavericks.app --nointeraction
```

And I still got the same error.

Then I wondered whether the `createinstallermedia` file was *actually* executable. If you pass just a normal file to `sudo` (rather than an executable program) “command not found” is exactly the sort of cryptic error you’d expect. I checked and—*lo and behold*—my copy of `createinstallermedia` wasn’t executable after all.

Easily fixed:

```sh
sudo chmod +x /Applications/Install\ OS\ X\ Mavericks.app/Contents/Resources/createinstallmedia
```

Once `chmod` has made the file executable, Diskmaker X was happy again, and my OS X 10.9 installer drive was set up in about 25 minutes.

{% img "Diskmaker X" "/media/diskmaker-x-success.jpg" %}

I have no idea why `createinstallermedia` wasn’t executable in my version of the installer–maybe it has something to do with me storing the installer on an external disk for the best part of a year, but at least it was a simple fix once I worked out what was going on.
