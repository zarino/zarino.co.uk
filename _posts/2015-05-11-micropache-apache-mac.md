---
layout: post
title: "Micropache: Cut the crap out of getting Apache running on your Mac"
summary: >
  Upgraded to a new version of Mac OS X but can’t face the nightmare of getting your Apache-PHP-MySQL stack working? Try Micropache, my one-line Apache server.
related:
  - /post/git-push-to-deploy
  - /post/standing-desk
---

I recently—<em>finally</em>—upgraded my Mac from OS X 10.7 Lion to 10.10 Yosemite. Over the years, I’d become resigned to the fact that Apache is a **pain in the ass** to get running after *any* OS X upgrade.

I develop on Apache/PHP sites so infrequently that it really doesn't make sense for me to go [rummaging around system config files](http://jason.pureconcepts.net/2014/11/install-apache-php-mysql-mac-os-x-yosemite/#additional-configuration-optional) and [setting up Virtual Hosts](http://jason.pureconcepts.net/2014/11/configure-apache-virtualhost-mac-os-x/) for each site. Nor am I *really* comfortable running [MAMP](https://www.mamp.info/en/), which requires me to fumble with buttons and checkboxes every time I want to start work.

Since I’m *already* in a terminal window—editing files with TextMate and managing source code with `git`—it makes sense to run an Apache server the same way.

In any other language or framework (eg: Python, Ruby, Django, Jeykll…) starting a development server in the current directory is as easy as running a single command. In Python, for example, it’s:

    cd ~/projects/some-website.org/
    python -m SimpleHTTPServer

In Jekyll it’s:

    cd ~/projects/some-website.org/
    jekyll serve

In Rails it’s… you get the idea.

So it hit me, why spend hours (days?) hacking my Mac to run Apache virtual hosts, when I could instead just fire up an Apache daemon in the current directory, serve the files, and be done with it? One command. Simples.

## Turns out it really *is* that simple

The `apachectl` command doesn’t let you run a new server from a given directory, but the lower-level [`httpd`](http://httpd.apache.org/docs/2.2/programs/httpd.html) command does. Its arguments are pretty gnarly, and it needs to be provided with a config file (this is Apache after all!). So I wrote something that wraps it all up into a single command: [Micropache](https://github.com/zarino/micropache).

Now when I come to work on a WordPress site, for example, I `cd` into the project directory and run `micropache`.

It asks for my root password (getting Apache to run on a Mac without root privileges was a challenge I didn’t have time to face) and then starts serving the files at <http://localhost> on port 80.

    cd ~/projects/some-website.org/
    micropache
    Password:
    [Mon May 11 08:52:53 2015] [mpm_prefork:notice] [pid 39321] AH00163: Apache…
    [Mon May 11 08:52:53 2015] [core:notice] [pid 39321] AH00094: Command line:…

Each HTTP request is logged to the console, and when I’m done, `ctrl-C` will quit the server, as you’d expect.

The whole script took me about an hour to write – but it’ll only take you ten seconds to install: [github.com/zarino/micropache](https://github.com/zarino/micropache).

Combined with `brew install homebrew/php/php56` and `brew install mysql`, you can basically outsource all the customary headaches of getting a local MAMP server running on a new Mac, without ever leaving your terminal.

![Micropache](/media/micropache.png)
