---
layout: post
title: "Controlling the fan curve of an AMD GPU on Pop!_OS (or other Ubuntu-like operating systems)"
summary: >
  The open-source amdgpu driver doesn’t come with any visual controls over fan speeds and overclocking. But with a little bit of Linux know-how, you can create your own system service to monitor and adjust fan speeds to your liking.
related:
  - /post/debugging-crappy-internet/
  - /post/imagemagick-ffmpeg/
---

Around this time last year, I put together a small computer for playing games on my living room TV. I snagged a [Sapphire Vega 56 Pulse GPU](https://uk.pcpartpicker.com/product/cKhKHx/sapphire-radeon-rx-vega-56-2gb-pulse-video-card-11276-02-40g) for a really good price on ebay, combined it with [a mid-range i5 CPU and a few other bits](https://uk.pcpartpicker.com/user/zarino/saved/sR8Jbv), and I was laughing. The computer runs the Ubuntu-like operating system [Pop!_OS](https://system76.com/pop).

Out of the box, my Vega 56 had a fairly unusual fan curve. At low loads, it wouldn’t run the fans full time, even at a slow speed – instead, it would intermittently _pulse_ (ha!) the fans, for a few seconds at a time, then stop for a little while.

Sometimes, when playing games complex enough to work the GPU a bit, but not enough to trigger an always-on fan, the GPU would warm up to the point of—I assume—hitting some sort of internal safety mechanism, and cut off video output. Which was not ideal.

I figured it was time to set a proper fan curve on the GPU, so that the fans were always spinning, albeit slowly, even at idling loads, so that the temperatures never got a chance to creep up.

Around the same time, someone on the [AMD Reddit forums](https://www.reddit.com/r/Amd/comments/ctwez5/announcing_corectrl/) was sharing a new program they’d written, [called CoreCtrl](https://gitlab.com/corectrl/corectrl), which was meant to make monitoring and controlling your GPU and CPU stats really easy. Kind of like an open source equivalent to AMD’s Windows-only [WattMan](https://www.amd.com/en/technologies/radeon-wattman).

{% img src="/media/corectrl.jpg" %}

One of the things CoreCtrl lets you do is set a custom fan curve for your GPU, on a nice point-and-click line graph. Awesome!

The downside, however, is that CoreCtrl has to be running to control that curve. I spent a while with [CoreCtrl set as a startup application](https://gitlab.com/corectrl/corectrl/-/wikis/Setup), but it was annoying having to close the window each time my computer finished starting up.

I figured there had to be a better way.

Turns out there is.

---

## How to control AMD GPUs on Linux

By default, on Pop!_OS (and, I assume, other low-config Ubuntu-like operating systems), if you’ve got an AMD GPU, you’ll be using AMD’s open source [`amdgpu` driver](https://wiki.archlinux.org/index.php/AMDGPU).

Following Unix’s [“everything is a file”](https://en.wikipedia.org/wiki/Everything_is_a_file) ideology, the `amdgpu` driver exposes a bunch of monitoring and control endpoints via special files at `/sys/class/drm/card0/device/`.

You can _read_ a file like `/sys/class/drm/card0/device/hwmon/hwmon0/temp1_input` to find out the GPU’s current temperature (in millidegrees Celsius, eg: `51000` for 51°C), and you can _write_ a number to a file like `/sys/class/drm/card0/device/hwmon/hwmon0/pwm1` to set the fan speed (as an 8-bit binary number, so `0` for fans completely off, up to `255` for fans completely on).

What the `amdgpu` driver _doesn’t_ expose is a file to set a series of temperatures/fan speeds on a curve. So, if you want to change the fan speed based on the GPU’s temperature, you have to write a script that runs in the background and monitors and sets the speed, automatically.

Yeesh, hard work.

Thankfully, however, lots of other people have already done this work for you. Yay Open Source!

There’s [this one in Python](https://github.com/chestm007/amdgpu-fan). And [another in bash](https://github.com/grmat/amdgpu-fancontrol).

I’m a sucker for a well written bash script, so I went with that one.

---

## Setting up amdgpu-fancontrol

I took a look at my CoreCtrl config, and jotted down the parameters of the fan curve it had been setting:

<table class="table">
<thead>
<tr><th>Temperature</th> <th>Fan speed</th></tr>
</thead>
<tbody>
<tr><td>35°C</td> <td>20%</td></tr>
<tr><td>52°C</td> <td>22%</td></tr>
<tr><td>67°C</td> <td>30%</td></tr>
<tr><td>78°C</td> <td>50%</td></tr>
<tr><td>85°C</td> <td>82%</td></tr>
</tbody>
</table>

I figured now was an opportunity to get the cool end of that curve as quiet as possible, so I experimented a bit to see how low I could set the fan speed without it stopping completely. It turned out a speed of about 17% did the job.

Converting to millidegrees Celcius and an 8-bit binary PWM value, I got:

<table class="table">
<thead>
<tr><th>Temperature</th> <th>Fan PWM</th></tr>
</thead>
<tbody>
<tr><td>35000</td> <td>45</td></tr>
<tr><td>52000</td> <td>56</td></tr>
<tr><td>67000</td> <td>76</td></tr>
<tr><td>78000</td> <td>128</td></tr>
<tr><td>85000</td> <td>210</td></tr>
</tbody>
</table>

I knew I’d be setting [systemd](https://en.wikipedia.org/wiki/Systemd) to run the `ampgpu-fancontrol` as root when the computer starts, so I made a decision _not_ to put any of the `ampgpu-fancontrol` files in my user’s home directory – just in case, you know, I eventually create a second user on the machine, and want _them_ to enjoy a non-crashing GPU too.

There are loads of places you could clone the `amdgpu-fancontrol` to, outside of your home directory. I picked `/usr/local/src`.[^1]

[^1]: Because `/usr` is owned by `root`, I have to use `sudo` a lot here. I guess I could have activated a root shell with `sudo su`, but I prefer staying in my regular shell.

First step – clone the repo:

```sh
cd /usr/local/src/
sudo git clone https://github.com/grmat/amdgpu-fancontrol.git
```

Then I wrote my fan speed values into a config file:[^2]

```sh
echo 'TEMPS=( 35000 52000 67000 78000 85000 )' | sudo tee /usr/local/src/amdgpu-fancontrol/amdgpu-fancontrol.cfg > /dev/null
echo 'PWMS=( 45 56 76 128 210 )' | sudo tee --append /usr/local/src/amdgpu-fancontrol/amdgpu-fancontrol.cfg > /dev/null
```

[^2]: Note [the use of `sudo tee` here](https://stackoverflow.com/questions/764463/unix-confusing-use-of-the-tee-command), to append output to a write-protected file. The first time, I use it without `--append`, to replace the entire content of the file (in case it already exists). The second time, I `--append` to just add the second line onto the end of the file.

Then I symlink that config file to the place that `amdgpu-fancontrol` expects to find it:

```sh
sudo ln -s /usr/local/src/amdgpu-fancontrol/amdgpu-fancontrol.cfg /etc/amdgpu-fancontrol.cfg
```

(Reminder: `ln -s` works just like `cp` – the original file goes first, and the new file you want to create goes second.)

I wanted to use the `amdgpu-fancontrol.service` file that came with the script, so I needed to also symlink the `amdgpu-fancontrol` script into `/usr/bin`, which has the added benefit of also putting the script on my `PATH` if I ever want to run it manually:

```sh
sudo ln -s /usr/local/src/amdgpu-fancontrol/amdgpu-fancontrol /usr/bin/amdgpu-fancontrol
```

Finally, I symlink the service file into place, and tell systemd to enable it (for the next boot) and also start it immediately:

```sh
sudo ln -s /usr/local/src/amdgpu-fancontrol/amdgpu-fancontrol.service /etc/systemd/system/amdgpu-fancontrol.service
sudo systemctl enable amdgpu-fancontrol.service
sudo systemctl start amdgpu-fancontrol.service
```

My GPU’s fans started purring, at their most whisper-quiet setting, so I knew my script was working its magic.

Running a very basic GPU stress test in one window:

```sh
vblank_mode=0 glxgears
```

And monitoring the output of the `amdgpu-fancontrol` service in another:

```sh
sudo journalctl --follow -u amdgpu-fancontrol.service
```

I could see `amdgpu-fancontrol` correctly updating the fan speeds as the temperature rises, and then backing them down once the stress test ended.

A quick restart, and I’d verified that my systemd service was starting automatically on boot. Job done!

---

## Useful links

If you want to simplify running most of the commands above, [Manuel Grießmayr](https://twitter.com/dc_coder_84) kindly [bundled them all into a `Makefile`](https://twitter.com/dc_coder_84/status/1663234485813493778) that you can run with `make install`, `make update`, and `make uninstall`. Twitter messed up the indentation, so here it is in full:

```make
.PHONY: install update uninstall

install:
	cp amdgpu-fancontrol.cfg /etc
	cp amdgpu-fancontrol /usr/bin
	cp amdgpu-fancontrol.service /etc/systemd/system
	systemctl enable amdgpu-fancontrol
	systemctl start amdgpu-fancontrol

update:
	cp amdgpu-fancontrol.cfg /etc
	systemctl restart amdgpu-fancontrol

uninstall:
	rm /etc/amdgpu-fancontrol.cfg
	rm /usr/bin/amdgpu-fancontrol
	systemctl stop amdgpu-fancontrol
	systemctl disable amdgpu-fancontrol
	rm /etc/systemd/system/amdgpu-fancontrol.service
```

If you want to find out more about how all of the amdgpu stuff works, the Arch Linux wiki is a treasure trove of really high-quality information:

* [AMDGPU – ArchWiki](https://wiki.archlinux.org/index.php/AMDGPU)
* [Fan speed control – ArchWiki](https://wiki.archlinux.org/index.php/Fan_speed_control#AMDGPU_sysfs_fan_control)

If you want to learn more about systemd files, this DigitalOcean tutorial is really well written:

* [Understanding Systemd Units and Unit Files – DigitalOcean](https://www.digitalocean.com/community/tutorials/understanding-systemd-units-and-unit-files)
