---
layout: post
title: Stop wasting time with Git branch upstreams
summary: >
  A three-character fix for the infuriating “no tracking information” Git error. Plus some bonus aliases to turn you into a Git ninja.
related:
  - /post/git-push-to-deploy/
  - /post/using-spotlight-mdfind/
---

I routinely work with about two dozen Git repositories at mySociety, spread across Github and our own in-house Git server. Inevitably, every few weeks, I’ll try to push or pull a repo and be greeted with this old favourite error:

~~~
$ git pull
There is no tracking information for the current branch.
Please specify which branch you want to merge with.
See git-pull(1) for details

    git pull <remote> <branch>

If you wish to set tracking information for this branch you can do so with:

    git branch --set-upstream-to=origin/<branch> master
~~~

Great. Thanks Git. That’s totally not useful.

You *could* copy and paste the command they suggest, remembering to update `<branch>` to match the current branch name. But we can do better than that.

## Next time, save yourself the hassle. Set up a Git alias that does it automatically!

Open up your `~/.gitconfig` file, and add a new `set-upstream` line to the `[alias]` section, like so:

~~~
[alias]
  …
  unstage = reset HEAD
  undo-commit = reset --soft HEAD^
  poh = push origin HEAD
  pulloh = pull origin HEAD
  set-upstream = !git branch --set-upstream-to=origin/`git symbolic-ref --short HEAD`
~~~

Now, when you see the “no tracking information” error, you can just type `git set`, tab complete the rest of the command, and you’re away.

It automatically assumes your local and remote branches are called the same thing (they usually are) and that you want to deal with a remote called `origin` (which you usually do). But if you don’t, you can always edit the alias to suit your workflow.

Plus, as a Christmas bonus, I’ve included in the above snippet some of the other shortcuts I routinely use:

* `poh` is a god-send for quickly and explicitly pushing code to the right remote and branch.
* `undo-commit` has saved me from embarassing reparatory commits more than once, by removing my latest commit – it leaves everything staged, ready for amendments, but you can combine it with `unstage` if you want to break up an accidentally overzealous commit into smaller chunks.

You’re welcome!
