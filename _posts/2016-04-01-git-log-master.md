---
layout: post
title: "10 second Git tip: What’s changed since master?"
summary: >
  I switch between a number of git projects all the time, and frequently need to remind myself what work I’ve already completed in my branch. This quick command helps me out every time!
related:
  - /post/jekyll-local-network/
  - /post/git-set-upstream/
---

All the work we do at [mySociety](https://mysociety.org) is constructed in work-in-progress branches, until it has been reviewed and merged into master. As a front-end guy at mySociety, I tend to jump a lot between projects, and often need to remind myself what changes I’ve made in a given branch, and how far I’ve got towards the end goal.

I find the default `git log` output pretty unwieldy for this purpose. Instead, I ask Git to show me *only the commits made since the current branch diverged from the master branch*. Like this:

    $ git lg master..

Where `git lg` is an alias in my `~/.gitconfig` file, like this:

    [alias]
      lg = log --abbrev-commit --date=short --pretty=tformat:'%C(yellow)%h %C(cyan)%ai%C(red)%d%Creset %s %C(green)<%an>%Creset'

{% img "Two panels of switches on a wall" "/media/git-lg-master.png" %}

It works even if your local copy of master contains commits that were made *after* your branch was started – you’ll just see all the commits made since your branch diverged from master.
