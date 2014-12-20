# Restoring your custom software after a Synology DSM update

A few weeks ago, I finally got round to updating my Synology DS214se NAS from DSM 4 to DSM 5.[^1]

[^1]: DSM (Disk Station Manager) is the Linux-based operating system that Synology devices run. Its most obvious manifestation is the browser-based GUI it gives Synology users to manage their devices. But DSM also intertwines with the underlying Linux operating system, so a major DSM update can have significant impact on command line access, if you’re accustomed to SSHing into your Diskstation.

The update process was relatively painless, but when I came to [back up my videos onto an external USB drive](/post/ds214se-under-the-hood) I found the `screen` command was no longer recognised.

I’d heard that DSM updates could mess with previously-installed custom packages on your Diskstation, and being a Mac user, I’m used to operating system updates scuppering the “unsupported” settings or software I might have installed on my Mac.

So I headed over to [my blog post on the subject](/post/ds214se-under-the-hood), to get `screen` installed again. But then I thought: **what if my custom programs are there, but they’re just inaccessible?**

I found out that `ipkg` installs its software in the `/opt/bin` directory, and sure enough, `/opt/bin` was there. But when I printed out the `$PATH` (the list of places my SSH user account expects to find programs to run), `/opt/bin` wasn’t there.

![Custom programs installed in /opt/bin but not accessible on the $PATH](/media/synology-opt-bin-screen.png)

It looks like the DSM update just overwrote the `~/.profile` file that adds `/opt/bin` to the `$PATH`. To get my programs running again, I just needed to add it back again.

DSM comes with only one text editor by default: `vi`. Use it to open the `~/.profile`…

```
diskstation> vi ~/.profile
```

Add `:/opt/bin` to the end of the line that defines the `$PATH` environment variable, and save and close the file.[^2]


[^2]: If you’re lost, use `i` to enter insert mode, move to the end of the line that starts with `PATH=`, type `:/opt/bin`, then, once it’s added properly, press the Escape key to exit insert mode, then type `:wq` to save the file and quit.

All the programs in `/opt/bin` will now be accessible in the terminal as normal – next time you SSH in. Your current SSH session will still be using the old value of `$PATH`. To reload it, run your `~/.profile` script manually…

```
diskstation> source ~/.profile
```

Sorted! — Or, not quite. When I tried to actually run the `screen` program, I got another error:

```
diskstation> screen
Cannot find termcap entry for 'xterm-256color'.
```

Tedious! Looks like the DSM update broke something else too. Like the previous problem, it was down to the `~/.profile` being overwritten. Adding an explicit value for the `$TERM` environment variable makes `screen` happy again. So repeat the `vi` steps above, except this time, add a new line at the bottom of the file:[^3]

```
export TERM=xterm
```

[^3]: The eagle-eyed amongst you will notice `~/.profile` already contains two lines that set the `$TERM` variable. If you want, you could amend them directly, rather than adding a new `export TERM` at the end of the file. But my way is simpler for newbies, and works just as well.

Now, when you refresh the session with `source ~/.profile` again, `screen` will open up just fine, and you can get back to whatever you wanted to do before.

**The moral of the story is:** DSM updates don’t seem to uninstall custom software from `/opt/bin` but they do overwrite `~/.profile` so any modifications you’ve made to your environment might need to be re-made after the update.

<link href="/post/ds214se-under-the-hood">
<link href="/post/write-hfs-synology-nas">
<meta name="description" content="Updated your Synology NAS, and suddenly things are broken when you SSH in. Try this quick trick for setting things straight again.">
