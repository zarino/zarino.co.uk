---
layout: post
title: "Altering the “Added” date for video files in Synology Video Station"
summary: >
  By default, Video Station sorts your library in “Recently added” order. But this order can get messed up when you migrate disks or re-index your library. Here’s how you can fix it with a little bit of SQL.
related:
  - /post/ds214se-under-the-hood/
  - /post/custom-software-synology-dsm-update/
---

I love Synology Video Station. I use it all the time to stream my library of video files from my NAS, to the DS Video app on my TV.

{% img "DS Video running on an Android TV" "/media/ds-video-android-tv.jpg" %}

The thing is, I recently upgraded from a DS214se to a DS218+. As part of [the migration](https://www.synology.com/en-us/knowledgebase/DSM/tutorial/General/How_to_migrate_between_Synology_NAS_DSM_6_0_and_later), it looks like Video Station re-indexed my video library, and picked totally the _wrong_ “date addded” for each video file. Suddenly, Video Station was showing all my files in a crazy random order, rather than putting the newest videos at the top. Sigh.

The Video Station web interface doesn’t let you modify a video’s “date added” field. But I knew the data had to be stored _somewhere_ on disk, so I went hunting for it.

## PostgreSQL and the `video_metadata` database

It turns out, under DSM 6 at least, Video Station stores all of its data in a PostgreSQL database called `video_metadata`, on your NAS. Your regular user account on the NAS (eg: `admin`) probably won’t have permission to access the Postgres database, but the `root` user does:

    # On your NAS…
    sudo su
    psql -U postgres -d video_metadata

I took a quick poke around the database to familiarise myself with Video Station’s data structure. Key points:

* The base item in a Video Station database is a `video_file` – Video Station records a file’s location, resolution, encoding… all sorts of useful stuff, along with its `create_date` and `modify_date`.
* Video Station classifies your video files into “types”: `movie`, `tvshow_episode`, `home_video`, etc. So each video file has a corresponding record in one of these tables, storing information specific to the file’s “type” – for example, a `tvshow_episode` has a `title` and `description`, a `tvshow_id` (for the series as a whole), `season` and `episode` number, as well as a `create_date` and `modify_date`.
* Records in the `video_file` table are linked to records in one of the “type” tables, via the `mapper_id` key, and the `mapper` table. It’s all quite tidy.

{% img "Video Station sort order" "/media/video-station-sort-order.jpg" %}

Experimentation revealed that, when the Video Station UI says it’s sorting your movies or TV episodes by “Recently Added”, it’s _actually_ sorting by the `create_date` field in the relevant “type” table. In other words, when you’re looking at your “recently added” movies, it’s running a query like…

    # In a psql shell…
    select * from movie sort by create_date desc;

It doesn’t look like the `create_date` and `modify_date` fields of records in `video_file` are actually used anywhere. But for tidyness, I guess we should set them to the same thing as the associated `movie`, `tvshow_episode`, or whatever.

## Resetting the `create_date` to match filesystem modification times

If you’re anything like me—throwing video files onto your NAS and never touching them again—then the last modification date of the files is probably pretty similar to the date those files were first “added” to Video Station.

So I figured, I could reset the `create_date` in the Video Station database to match the modification date of the files on disk. Easy peasy!

Although most people reach for the `stat` command to show the modification date (and lots else) of a file,[^1] if you’re only after the modification date itself, the `date` command actually gives you cleaner output, and allows you to format the date however you like:[^2]

    # Display the last modification time of a file
    date -r '/some/video/file.mp4' -F -u '+%Y-%m-%d %H:%M:%S'

[^1]: <http://man7.org/linux/man-pages/man1/stat.1.html>
[^2]: <http://man7.org/linux/man-pages/man1/date.1.html>

Modifying these database records by hand would have taken forever, so instead, [I wrote a quick shell script to do it for me](https://gist.github.com/zarino/b1ae16fed7e87627ba0c7f704f7d9129):

<script src="https://gist.github.com/zarino/b1ae16fed7e87627ba0c7f704f7d9129.js"></script>

My script is written to work on a single video file at a time. But by executing it as part of a `find` command, you can automatically run it on every video file in a directory:

    sudo su
    find /volume1/movies -type f \( -name '*.avi' -o -name '*.mov' -o -name '*.mkv' -o -name '*.mp4' -o -name '*.m4v' \) -exec ./set_video_metadata_date_created.sh {} \;

Once that’s done, reload the DS Video app on your device, and all your videos will be back in a sensible order!