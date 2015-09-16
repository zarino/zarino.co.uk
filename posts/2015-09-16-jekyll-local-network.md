# Accessing a Jekyll site over your local wifi network

[Jekyll](http://jekyllrb.com) is a simple framework for generating static websites and blogs. Static websites are all the rage these days because they load *way* faster than dynamically generated sites (eg: sites built on WordPress or Rails) and they can be hosted *entirely for free* at places like [GitHub Pages](https://pages.github.com).

![Jekyll and Hyde](/media/jekyll-hyde.jpg)

When you’re developing your Jekyll site, you can test it out locally using Jekyll’s built-in server:

```
$ cd ~/my-site
$ jekyll serve
Configuration file: /Users/zarinozappia/my-site/_config.yml
            Source: /Users/zarinozappia/my-site
       Destination: /Users/zarinozappia/my-site/_site
      Generating... done.
    Server address: http://127.0.0.1:4000/
  Server running... press ctrl-c to stop.
```

And ta-da! Your site is accessible in a web browser at <http://127.0.0.1:4000> or <http://localhost:4000> (because “localhost” is an alias for “127.0.0.1” in most people’s hosts file).

## But what about accessing the site from other devices on your network?

These days, you’d be mad to only test a website on the computer you developed it on. Mobile devices like phones and tablets account for over 50% of web traffic, and chances are you already have one in your pocket that you could test with.

The problem is, just connecting your iPad to the same wifi network as your Mac, and assuming that <http://127.0.0.1:4000> will work isn’t enough.

Even if you substitute in your Mac’s Bonjour name (like `zarinos-mac.local`) or local IP address on the wireless router’s network (like `10.10.10.1`) it still won’t work.

That’s because, even though the web request *is* being routed to the device with the address “10.10.10.1” or whatever, the Jekyll command line server only responds to requests for “127.0.0.1” so it doesn't grace your iPad with a reply, and you get an error like **“Safari cannot open the page because it could not connect to the server”**.

![Dr Jekyll says: Cripes old fellow, what a MONSTROUS predicament](/media/jekyll-monstrous-predicament.jpg)

## Solution: Tell Jekyll which hostname to respond to

Once you’ve twigged what’s going on, it’s quite simple: Just tell Jekyll to use your computer’s actual local IP address[^1] (like `10.10.10.1`) rather than `127.0.0.1`.

[^1]: On a Mac, you can find your computer’s local IP address from the “Network” pane in System Preferences, or by alt-clicking the wifi icon in the menubar. Or you can run `ipconfig getifaddr en0` in the Terminal (where `en0` is the wifi interface on a Mac without an Ethernet port – you might need a different number like `en1` if your Mac has built-in Ethernet, running plain `ipconfig` will help you out).

Jekyll has a `--host` command line flag for just this occasion:

```
$ jekyll serve --host 10.10.10.1
Configuration file: /Users/zarinozappia/my-site/_config.yml
            Source: /Users/zarinozappia/my-site
       Destination: /Users/zarinozappia/my-site/_site
      Generating... done.
    Server address: http://10.10.10.1:4000/
  Server running... press ctrl-c to stop.
```

The downside is that this IP address will change depending which wireless network you’re on.

But the upside is that, when other devices actually send HTTP requests to your IP address (“10.10.10.1”) Jekyll will actually respond, and you’ll be able to test your Jekyll site out on your iPhone or iPad, over wifi, without having to deploy it somewhere public first.

![Mr Hyde growls: Hey babe, fancy some cross-browser testing?](/media/jekyll-hyde-cross-browser.jpg)

Even if you *don’t* need to test your sites on hardware devices like phones and tablets, this trick works exactly the same for virtual machines – like [the ones Microsoft provides](https://github.com/xdissent/ievms) to run old browsers like IE7, 8, and 9 in a virtual version of Windows XP.

A VM is normally part of your local network just like any other device. Boot it, fire up Internet Explorer, and type in host’s IP address. Boom!


<link href="/post/which-process-is-using-port">
<link href="/post/music-to-program-to">
<meta name="description" content="We all know you can access a Jekyll site running locally at a address like http://localhost:4000 – but what about testing it from other devices on the network, like phones, tablets, and virtual machines?">
