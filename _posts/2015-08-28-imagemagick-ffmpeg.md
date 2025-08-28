---
layout: post
title: "ImageMagick and FFmpeg: manipulate images and videos like a ninja"
summary: >
  A collection of really useful command line snippets for manipulating and converting images and videos in a flash.
related:
  - /post/micropache-apache-mac/
  - /post/which-process-is-using-port/
---

ImageMagick (accessible via the command line program `convert`) is a software suite for converting and editing images. And FFmpeg (aka `ffmpeg`) is pretty much the same but for videos.

And, oh my God, they’re handy.

I probably use one or other of the tools every week – either at work with [mySociety](https://mysociety.org), or personally, converting videos for the [media server on my Synology NAS](/post/getting-started-ds214se-nas).

<hr class="stars">

For a while, I had a text note on my laptop with a bunch of my most commonly used `convert` and `ffmpeg` command line snippets. But [open is better](https://www.gov.uk/design-principles#tenth) (and not to mention, easier to find) so here it is. I’ll update the list over time, as I discover new tricks. If you have any suggestions, [tweet them to me](https://twitter.com/zarino)!

### Resize an image to fit within the specified bounds

```sh
convert before.png -resize 500x500 after.png
```

### Concatenate a series of images (before1.png, before2.png…) into an animated gif

```sh
convert -delay 100 -loop 0 before*.png after.gif
```

`-delay` is specified in 1/100ths of a second, and a `-loop` value of `0` means loop forever.

### Convert one media format into another

```sh
ffmpeg -i before.avi after.mp4
```

The input and output formats are auto-detected from the file extensions.

This command even works with weird formats like WMV and [loads of others](http://stackoverflow.com/questions/3377300/what-are-all-codecs-supported-by-ffmpeg), even on a Mac:

```sh
ffmpeg -i before.wmv -c:v libx264 -c:a libfaac -crf 23 -q:a 100 after.mp4
```

Because mp4 is a so-called [wrapper format](https://en.wikipedia.org/wiki/Digital_container_format), you can choose the specific video and audio codecs via `-c:v` and `-c:a` respectively. In this case we’ve asked for H.264 video and AAC audio. `-q:a` specifies the [AAC audio quality](https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac), and `-crf` is the [H.264 video quality](https://trac.ffmpeg.org/wiki/Encode/H.264).

### Convert the audio format of a file, leaving video format as-is

Sometimes, you’ll have device (like a TV) that can play the images in a video fine, but can’t play the audio. Or vice-versa.

In cases like this, you can save time by only transcoding the channel that’s causing a problem – leaving the other channel(s) unchanged. For example, to convert a mkv file containing h264 video (or whatever) and some unknown audio format, into an mkv file with the same h264 video channel but *AAC* audio:

```sh
ffmpeg -i before.mkv -c:v copy -c:a libfaac -q:a 100 after.mkv
```

`-c:v copy` tells ffmpeg to just use whatever video codec the original file uses, while `-c:a libfaac` tells it to convert the audio channel into AAC. In this case, we specify a fairly low audio quality using `-b:a 128k` (`-b` for bitrate).

### Extract the audio from a video

```sh
ffmpeg -i before.avi -vn -ab 256 after.mp3
```

`-vn` disables video output, and `-ab` specifies the audio bit rate.

### Convert all the FLAC files in the current directory into MP3s

```sh
for i in *.flac; do ffmpeg -i "$i" -q:a 0 "${i%.flac}.mp3"; done
```

`-q:a` specifies [MP3 audio quality](https://trac.ffmpeg.org/wiki/Encode/MP3), where `0` is a variable bit rate of 220–260 kbit/s. `${i%.flac}` is a [Bash operator](http://tldp.org/LDP/abs/html/refcards.html#AEN22664) that returns the variable `i` without the `.flac` at the end.
