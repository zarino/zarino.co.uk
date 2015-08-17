# A Synology-flavoured rsync backup script

I store lots of stuff on my [Synology NAS](/post/getting-started-ds214se-nas): videos, TV shows, music. Every now and then, I dump the entire contents onto an [external USB drive](/post/write-hfs-synology-nas) – partly as a second backup, and partly because it means I can take my stuff with me when I travel on business or visit my family at home.

![Synology DS214se](/media/ds214se.jpg)

Last night I went through the process of cloning my media files over onto the external USB drive, and I figured I might as well publish my script for doing it.

For the impatient here’s the script:

```
#!/bin/sh

rsync --archive --progress --verbose --inplace \
--exclude '*@SynoResource' --exclude '@eaDir' \
--exclude '*.vsmeta' --exclude '.DS_Store' \
/volume1/files/ /volumeUSB1/usbshare1-2/
```

It’s a single `rysnc` command, albeit broken onto a few lines for legibility. The backslashes at the end of each line escape the newline characters, meaning the command runs as if everything were written on one line.

The command copies the entire contents of `/volume1/files/` into `/volumeUSB1/usbshare1-2/` – excluding any files that match the patterns specified in the `--exclude` arguments. In this case, I’ve excluded a bunch of files the Synology DSM creates automatically, along with `.DS_Store` files that Mac OS X makes when it opens folders in the Finder.

Any files in `/volumeUSB1/usbshare1-2/` that aren’t in `/volume1/files/` will remain untouched, so there’s no fear that it’s going to delete anything.

I store the script at `/volume1/files/clone-files-to-usb.sh`, then make it executable with:

```
$ chmod +x /volume1/files/clone-files-to-usb.sh
```

If I want to check whether the command will do the right thing, I add `--dry-run` and `--itemize-changes` to the command like so:

<pre>
rsync <b>--dry-run --itemize-changes \</b>
--archive --progress --verbose --inplace \
--exclude '*@SynoResource' --exclude '@eaDir' \
--exclude '*.vsmeta' --exclude '.DS_Store' \
/volume1/files/ /volumeUSB1/usbshare1-2/
</pre>

And then pipe the output to `grep`, to show only the files that’ll be copied to the USB drive:

```
$ /volume1/files/clone-files-to-usb.sh | grep '^>' > /tmp/rsync.out
$ more /tmp/rsync.out
```

(When `--itemize-changes` is on, files being copied from the source to the destination are printed out, with a `>` at the start of the line. The regular expression we pass to `grep` says “only match lines starting with a `>`”. Then we pipe all the output to a file, so we can browse it at our leisure, and regardless of how deep our `screen` scrollback buffer is.)

---

## Pulling it all together, my typical workflow is:

1. SSH into the diskstation [(how?)](/post/ds214se-under-the-hood)
2. Activate a screen session with `screen` [(how?)](/post/write-hfs-synology-nas#fn:1)
3. Re-mount the USB drive as a writeable HFS+ drive [(how?)](/post/write-hfs-synology-nas)
4. Start the backup with `/volume1/files/clone-files-to-usb.sh`
5. Disconnect from the screen session with `Ctrl-a d`
6. Disconnect from the diskstation with `Ctrl-d`

Then, the morning after, I’ll:

1. SSH back into the diskstation
2. Connect to the same screen session as before with `screen -x`
3. If everything went fine, type `exit` to close the screen session
4. All done!

<link href="/post/custom-software-synology-dsm-update">
<link href="/post/write-hfs-synology-nas">
<meta name="description" content="A good starting point if you routinely copy files from your Synology DiskStation NAS onto an external USB drive, or a remote server.">
