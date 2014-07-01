# Find out which process is using a port on a Mac

I’ve been using [Jekyll](http://jekyllrb.com/), the static HTML site generator, more and more these last few weeks – for both personal projects and at [mySociety](http://mysociety.org/) for sites like [Poplus](http://poplus.org/). It does 95% of what everybody would want, right out of the box, and the Github Pages integration makes for a killer zero-hassle deployment feature.

Every now and then, however, I’ll get a warning when I try to start up a local Jekyll server:

```
$ jekyll serve --watch
Configuration file: /Users/zarinozappia/…/_config.yml
            Source: /Users/zarinozappia/…
       Destination: /Users/zarinozappia/…/_site
      Generating... done.
 Auto-regeneration: enabled
error: Address already in use - bind(2). Use --trace to view backtrace
```

The error’s annoying, not only because it doesn’t tell you which port Jekyll’s trying to use (4000), but also because it doesn’t say what’s using the port, so you have no idea what to kill.

If you get faced with this message, and you’re not aware of running any (often Ruby) servers, then you probably have a stray process running somewhere.

Linux users will quickly turn to `netstat`, but netstat on the Mac is pretty useless. In this case, it’s simplest to just use `lsof` to find the offending process:

```
$ lsof -i tcp:4000
COMMAND   PID         USER   FD   TYPE
ruby    34868 zarinozappia    8u  IPv4
```

Then, to kill it, take the PID, and:

```
$ kill -9 34868
```

Done! Now your Jekyll server will start smoothly again.

```
$ jekyll serve --watch
Configuration file: /Users/zarinozappia/…/_config.yml
            Source: /Users/zarinozappia/…
       Destination: /Users/zarinozappia/…/_site
      Generating... done.
 Auto-regeneration: enabled
    Server address: http://0.0.0.0:4000
  Server running... press ctrl-c to stop.
```

<link href="/post/write-hfs-synology-mac">
<link href="/post/git-push-to-deploy">
