---
layout: post
title: "Setting up a Raspberry Pi from a Mac"
summary: >
  Quick notes on installing Raspbian and SSHing in over wifi on the first boot.
related:
  - /post/debugging-crappy-internet/
  - /post/imagemagick-ffmpeg/
---

As part of the same [hardware project I wrote about yesterday](/post/udmx-mac), we’re looking into using a Raspberry Pi as an [MQTT broker](https://en.wikipedia.org/wiki/MQTT) with a couple of [ESP32](https://en.wikipedia.org/wiki/ESP32) clients.

It turns out this is the first time I’ve ever actually had to set up a Raspberry Pi from scratch, and despite what I’d been led to believe, it’s annoyingly fiddly, especially if you want to connect to it over wifi on the first boot.

Here are the notes I made while I was learning. I expect I will need to remind myself of them at least a few times again until it becomes second nature!

---

## You will need:

* A Raspberry Pi with wifi access
* A microSD card
* A microSD card adaptor, or external card reader, for your Mac
* A wifi network

## Flash Raspbian onto the microSD card

1. Download <https://downloads.raspberrypi.org/raspbian_latest>
1. Download and install [balenaEtcher](https://www.balena.io/etcher/) if you don’t have it already.
1. Plug the microSD card into your Mac.
1. Open Disk Utility, and check the microSD card is FAT32 formatted. If it isn’t, erase/reformat it to FAT32.
1. Open balenaEtcher, select the `raspbian-stretch.zip` file you downloaded as the “image”, and select the microSD card as the “drive”. Then press “Flash!”
1. Once the flashing as finished, balenaEtcher will “helpfully” unmount the volume from your Mac. Mount it again by opening Disk Utility and clicking “Mount”, or just remove and reinsert the physical SD card.

## Enable SSH access to the Pi

1. Create file named `ssh` on the `boot` drive. The file doesn’t need any content. For example, you could open Terminal and type:

   ```sh
   touch /Volumes/boot/ssh
   ```

## Enable Wifi on the Pi

1. Create a text file called `wpa_supplicant.conf` on the `boot` drive, then open it in a text editor. For example, you could open Terminal and type:

   ```sh
   nano /Volumes/boot/wpa_supplicant.conf
   ```

2. Fill it with the following text, replacing the three placeholder values with your [ISO-3166-1 two-letter country code](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) (eg: `GB`), your wifi network SSID, and your wifi network password:

   ```conf
   ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev
   update_config=1
   country=«your_ISO-3166-1_two-letter_country_code»

   network={
       ssid="«your_wifi_ssid»"
       psk="«your_wifi_password»"
       key_mgmt=WPA-PSK
   }
   ```

## First boot

1. Eject the microSD card from your Mac, and insert it into the microSD slot on the Pi.
1. Connect the Pi to power.
1. Wait a few seconds for the Pi to boot.

You should be able to tell when the Pi has booted by running this command in Terminal on your Mac:

```sh
ping raspberrypi.local
```

Once the Pi has booted and joined your wifi network, you can SSH in with:

```sh
ssh pi@raspberrypi.local
```

The password for the default `pi` user is `raspberry`.

## Changing the hostname

If you want to change your Pi’s hostname from `raspberrypi.local` to something unique, you can do it once you’re SSHed in:

1. Switch to superuser mode:

   ```sh
   sudo su
   ```

1. Open `/etc/hostname` and replace `raspberrypi` with the hostname of your choice:

   ```sh
   nano /etc/hostname
   ```

1. Do the same with `/etc/hosts`, where `raspberrypi` appears somewhere near the end of the file:

   ```sh
   nano /etc/hosts
   ```

1. Reboot your Pi:

   ```sh
   reboot
   ```

Now `ssh pi@raspberrypi.local` won’t work any more, because your Pi has a new hostname. You’ll need to change your SSH command to use the new hostname, eg:

```sh
ssh pi@mylovelypi.local
```

When you’re SSHed in to the Pi, you can quickly check the current hostname by running:

```sh
hostname
```

## Changing the username and password

Changing the `pi` user’s password is easy. Once you’re SSHed in as the `pi` user, just run:

```sh
passwd
```

It’ll lead you through the process of setting a new password.

If you want to change the `pi` user’s _username_ (to something other than `pi`) then you’ll need to activate the `root` user, SSH in as that, and change the `pi` user’s username from there. [Detailed instructions here.](https://www.modmypi.com/blog/how-to-change-the-default-account-username-and-password)
