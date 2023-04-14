---
layout: post
title: "Mount a single drive from a two-disk Synology SHR RAID 1 group, on Pop!_OS (or another Ubuntu-like OS)"
summary: >
  In which Zarino recovers data from his parents’ dead two-disk DS214se, by mounting one of its drives read-only on his Pop!_OS PC.
---

A number of years ago, I upgraded from [my original DS214se NAS](/post/getting-started-ds214se-nas/) to a more powerful DS218+, donating the DS214se to my parents to act as a Time Machine backup drive and video server.

{% img alt="Synology DS214se" src="/media/ds214se.jpg" srcset="/media/ds214se.jpg 700w, /media/ds214se@2x.jpg 1400w" %}

My parents live in the most beautful corner of Herefordshire. The _one_ down-side of this location is very occassional blips in power supply (all the rural dwellers reading this will be nodding right now). Usually this doesn’t cause an issue. But over Christmas my parents told me they’d noticed the DS214se was stuck in some sort of loop, attempting to boot up again and again, but never completing. I figured it had improperly shutdown at some point before, and was now stuffed.

I tried [all the usual troubleshooting](https://kb.synology.com/en-global/DSM/tutorial/What_can_I_do_unresponsive_Synology_NAS), nothing worked.

It probably needs a reset and a clean pair of disks. But before I do that, I wanted to get a copy of everything on the _existing_ disks, just in case. I knew this wouldn’t just be a case of plugging the one of the SATA drives into my PC, though, because these drives had been configured as an [SHR (“Synology Hybrid RAID”) RAID 1 group](https://kb.synology.com/en-us/DSM/tutorial/What_is_Synology_Hybrid_RAID_SHR).

Synology publishes a guide on [mounting SHR disks on an Ubuntu PC](https://kb.synology.com/en-us/DSM/tutorial/How_can_I_recover_data_from_my_DiskStation_using_a_PC), but when I tried it, I got stumped by an error message from `mdadm`:

    root@pop-os:~# mdadm -AsfR && vgchange -ay
    mdadm: No arrays found in config file or automatically

I note Synology’s guide says you need _all_ of the disks from your SHR RAID group – but I only had one SATA cable, and I also quite liked the idea of leaving one disk entirely untouched, _just in case_ my attempts to mount the disks also fried them. I wanted to mount just _one_ of the _two_ disks.

(Theoretically, this _should_ work fine, since a two-disk Synology NAS in SHR mode will use RAID 1, which simply mirrors all the same content onto both disks – it _should_ be possible to mount any one of those disks individually.)

But how do you do it?

# What you’ll need

- An Ubuntu-like computer/environment (I used my Pop!_OS gaming PC, but [Synology’s guide includes links to setting up an Ubuntu Live USB drive](https://kb.synology.com/en-us/DSM/tutorial/How_can_I_recover_data_from_my_DiskStation_using_a_PC) if all you have is a Windows or Mac device)
- Any one of the two disks from your Synology’s SHR RAID 1 group
- A way to connect the disk to your computer (I used a USB-to-SATA cable, or you could plug the disk directly into your motherboard – note, 3.5 inch disks will need an external power connection)

As mentioned in Synology’s guide, you’ll need to install mdadm and lvm2 (my Pop!_OS system already had these installed):

    $ apt-get update
    $ apt-get install -y mdadm lvm2

# Mounting the disk

Get into an interactive root shell:

    $ sudo -i

Plug in and power up the SATA disk. With any luck it’ll spin up, and Ubuntu will start reading it.

At this point, you could try running the commands that Synology suggested – although, as I say, these didn’t work me:

    $ mdadm -AsfR && vgchange -ay

If it doesn’t work, instead try running `lsblk` to see what partitions are on the disk:

    $ lsblk

In my case, this was the output:

    NAME            MAJ:MIN RM   SIZE RO TYPE  MOUNTPOINTS
    sda               8:0    1     0B  0 disk  
    sdb               8:16   0   1.8T  0 disk  
    ├─sdb1            8:17   0   2.4G  0 part  
    ├─sdb2            8:18   0     2G  0 part  
    ├─sdb3            8:19   0     1K  0 part  
    └─sdb5            8:21   0   1.8T  0 part  
      └─md127         9:127  0     0B  0 md    
    zram0           252:0    0    16G  0 disk  [SWAP]
    nvme0n1         259:0    0 953.9G  0 disk  
    ├─nvme0n1p1     259:1    0  1022M  0 part  /boot/efi
    ├─nvme0n1p2     259:2    0     4G  0 part  /recovery
    ├─nvme0n1p3     259:3    0 944.9G  0 part  
    │ └─cryptdata   253:0    0 944.9G  0 crypt 
    │   └─data-root 253:1    0 944.8G  0 lvm   /
    └─nvme0n1p4     259:4    0     4G  0 part  
      └─cryptswap   253:2    0     4G  0 crypt [SWAP]

`nvme0n1` is my PC’s M.2 SSD drive (with the encrypted root partition on `nvme0n1p3` and the Pop!_OS recovery partition on `nvme0n1p2`).

`zram0` is the [system swap disk](https://en.wikipedia.org/wiki/Zram). And `sda` is empty.

So that just leaves `sdb` with a total capacity of `1.8T` (1.8 terabytes) – looks like my SATA disk! The `sdb5` partition, occupying almost all of that 1.8 TB drive, is the one with all the data on, and the one we’ll want to mount. But how do we mount it?

It turns out you can force `mdadm` to recognise just one of the two RAID 1 disks:

    $ mdadm --assemble --run /dev/md0 /dev/sdb5 --force

`/dev/md0` there is just a new device name for mdadm to assemble the RAID group into – you can pick whatever you like, but it seems `/dev/md0` is traditional. And `/dev/sdb5` is the partition we identified in the previous step.

Once you’ve run that, you’ll _hopefully_ get the golden response:

    mdadm: /dev/md0 has been started with 1 drive (out of 2).

(Top tip: if at any point you get into a bit of a mess, and run mdadm on a non-RAID partition, giving you `mdadm: [blah] is busy - skipping` errors, you can tell mdadm to stop with `mdadm --stop --scan`.)

So, we’ve got the RAID group assembled. Next we need to identify the [logical volumes](https://en.wikipedia.org/wiki/Logical_volume_management) inside it. If you run `lvs` or `lvscan` you’ll see all the available volumes on the computer:

    $ lvscan
      ACTIVE            '/dev/data/root' [<944.85 GiB] inherit
      WARNING: PV /dev/md0 in VG vg1000 is using an old PV header, modify the VG to update.
      ACTIVE            '/dev/vg1000/lv' [1.81 TiB] inherit

(You’ll also get a warning about "an old PV header", which [you _could_ fix with `vgck --updatemetadata vg1000`](https://access.redhat.com/solutions/5906681) but I decided not to, because everything seems to work fine without it, and I want to avoid changing anything on the disk unless I absolutely have to.)

Now we know what the volume group is called (`vg1000`) we can activate all of the logical volumes inside it:

    $ vgchange -ay vg1000
    1 logical volume(s) in volume group "vg1000" now active

And our volume is ready to mount!

Create a mountpoint for the volume (this could be a new directory anywhere on your computer – traditionally mountpoints go into `/mnt`, so I created mine at `/mnt/hd1`):

    $ mkdir /mnt/hd1

And then mount the volume, in read-only mode:

    $ mount /dev/vg1000/lv /mnt/hd1 -o ro

`/dev/vg1000/lv` is the logical volume path from the lvscan output above, while `/mnt/hd1` is the mountpoint we created earlier, and `-o ro` sets the "read-only" option, so we don’t accidentally modify anything on the disk.

You can now open up `/mnt/hd1` in the file explorer, and copy stuff from it!

When you’re done, you can unmount the volume:

    $ umount /mnt/hd1

Then deactivate the logical volume group:

    $ vgchange -an vg1000

And tell mdadm to stop:

    $ mdadm --stop /dev/md0

With the filesystems unmounted, you can “power off” the hardware device itself (to stop the disk spinning) with:

    $ udiskctl power-off -b /dev/sdb
