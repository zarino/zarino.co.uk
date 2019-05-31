---
layout: post
title: "Uninstalling a Mac kernel extension"
summary: >
  Notes from a late night dive into the world of renegade Mac kernel extensions, and how to get rid of them when they prevent your Mac from booting.
related:
  - /post/debugging-crappy-internet/
  - /post/imagemagick-ffmpeg/
---

**Recovering after a Kernel Extension (.kext) bricks your Mac**

Last night I had a pretty hair-raising experience with a [custom USB Serial driver](http://www.wch.cn/download/CH341SER_MAC_ZIP.html) (theoretically meant to allow me to program a cheap Arduino Nano clone, running a CH431/CH431 USB-to-Serial chip) that turned my Mac into a very expensive brick.

After the kernel extension installation completed, the Mac rebooted. I have full disk encryption turned on via FileVault, so the Mac booted fairly quickly into the usual login screen. But the progress bar after login was incredibly slow, eventually ending in a black screen, and the Mac booting into its Recovery Partition. Eeeek.

The log displayed in the Recovery Partition interface was useless, and didn’t seem to mention my new kernel extension at all, but I knew that must have been the cause.

Here’s how I tracked down the extension file, and got my Mac booting again:

---

## Safe boot

1. Turn the Mac off.
2. [Safe Boot](https://support.apple.com/en-us/HT201262), by starting your Mac and immediately holding down the Shift key until you see the login screen.
3. Pick a user and enter a password, as normal, at the FileVault login screen.
4. Wait a long time for the Mac to boot into the Safe Mode Desktop.

---

## Find out what was installed

You can either:

1. Find the installer file that started this whole mess. (It ends with `.pkg`… You kept it, right? 😉) And—even though this sounds crazy, trust me—open it again.
2. When the first screen of the installer is showing, press `⌘–I` to show the list of files the installer creates.
3. Quit the installer, without installing.

Or you can:

1. Open Terminal (you might need to go find it in Applications > Utilities, because I don’t think Spotlight works in Safe Boot mode) and type `pkgutil --packages` to list all packages installed on the system, in roughly—I think—date order.
2. Look through the package names, and once you find the suspicious one, find out which files it installed by typing, eg:

       pkgutil --files com.wch.ch34xinstall.mykextdir.pkg

---

## Remove what was installed

In my case, I used the `⌘–I` trick to get the file names, and I could see that the installer had created a `usbserial.kext` file, and a load of files inside that, but it didn’t say where the kext file was.

Long story short: it turns out Kernel Extensions can be installed into one of two places on a Mac – either `/Library/Extensions` or `/System/Library/Extensions`.

In my case, the `usbserial.kext` file was in `/Library/Extensions`. Yours might be different, so take a look in both places. Once you’ve found it:

1. Open up Terminal again, and move the extension somewhere safe, like your Desktop. (I prefer to do this, rather than deleting it, just in case I need to move it back!) Eg:

       sudo mv /Library/Extensions/usbserial.kext ~/Desktop/

2. “Touch” the `/Library/Extensions` directory, so that the Mac knows that the list of extensions has changed. Eg:

       sudo touch /Library/Extensions

3. In my case, because I was stressing out and wanted a belt-and-braces approach, I also forced the Mac to rebuild its cache of extensions, just in case it didn’t spot the earlier “touch”:

       sudo kextcache -invalidate /

4. Shut down the Mac.

---

## Reboot your Mac in normal mode, and check it works

1. Start the Mac as normal.
2. If you get logged into your normal Desktop, then hooray! You fixed it!
    * You can delete the kext file you stashed on your Desktop, eg:

          sudo rm -rf ~/Desktop/usbserial.kext

    * And if you want, you can tell your Mac to forget that it ever ran that cursed installer, eg:

          sudo pkgutil --forget com.wch.ch34xinstall.mykextdir.pkg

3. However, if you get booted into the Recovery Partition again, then I’m afraid I can’t help you any further 😞

Good luck!
