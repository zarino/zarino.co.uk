---
layout: post
title: New Year’s Resolution #1: Back up your shit
summary: >
  A New Year’s resolution we should all observe: what’s your computer backup solution, and if your main machine died tomorrow, how long would it take you to get up and running again?
related:
  - /post/getting-started-ds214se-nas/
  - /post/time-machine-ds214se-nas/
---

For two years I’ve had a [portable LaCie hard drive](http://www.engadget.com/products/lacie/porsche-design/p-9233/) in my satchel, storing all the movies that won’t fit on my MacBook’s SSD, and also running Time Machine backups whenever it’s plugged in.

Just before Christmas, like a proper numpty, I managed to push it off the edge of my desk. For a few weeks it flickered between life and death, but as the new year bells rang, it finally gave up the ghost.

I’m now backup-less[^1]. And it’s really scary.

{% img "Rose diagram of the contents of my hard drive - created by the Mac app Daisy Disk" "/media/daisy-disk.jpg" %}

Thankfully, though, it’s given me a chance to reappraise where I’m keeping all my stuff. The time has come, methinks, to separate _actual stuff_ from _backed-up stuff_. So when I replace my portable hard drive, I’ll be using it *only* for storing movies and files. The backups will go somewhere dedicated to the task (and less likely to get dropped from a great height).

I could just get a 2TB hard drive ([Western Digital’s MyBook series](http://www.wdc.com/en/products/products.aspx?id=870) has served me well, through three or four incarnations over the years). But what happens when that drive goes? (I seem to be the only person I know who _hasn’t_ yet had a Western Digital drive fail on them.)

It’s also annoying having to plug the damned thing in all the time. The [Apple Time Capsule](http://www.apple.com/uk/airport-time-capsule/) solves this by backing up wirelessly, but again, what happens when the drive inevitably fails?

So I figure it’s about time to back up my stuff like a proper grown up, with [a little RAID](https://en.wikipedia.org/wiki/RAID) like the 2-disk [Synology DS214se](http://www.synology.com/en-global/products/overview/DS214se). It’s compatible with Time Machine on OS X, and once it’s been fitted with a pair of 1TB or 2TB disks, it’s largely comparable with an Apple Time Capsule for both price and features. But with the added benefit that, if something goes wrong with one of the disks, I can just swap it out with no loss of data, and if I start running out of space, I can just buy bigger disks.

{% img "Synology DS214se disk enclosure" "/media/ds214se.jpg" %}

You can get larger, more expensive RAID controllers. But, frankly, I was amazed at how cheap and accessible this stuff has become. The only real shame is that the whole thing is a decision minefield. Synology alone sells 10 “home” RAID solutions – without even considering the competition. It’s a world of acronyms and long, complicated feature comparison charts. And most worryingly, there are surprisingly few end-to-end “here’s how I did it” articles by people who’ve bought one of these boxes and got it running with their existing stuff.

Hopefully, that last point is something I can remedy, if I go down the DS214 route. Stay tuned.

But in the meantime, go check your backups. If your computer died today, could you recover your stuff by tea-time? And if the answer’s yes, how about asking the same of your friends, or your parents?


[^1]: I tell a lie: my first step after the ’unfortunate incident’ was to take a whole disk snapshot of my Mac’s hard drive onto a second disk I had lying around. But it’s already two weeks out of date. And as any tech guy will tell you, a manual backup solution isn’t a backup solution at all.
