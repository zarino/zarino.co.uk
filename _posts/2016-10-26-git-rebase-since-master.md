---
layout: post
title: "10 second Git tip: Rebase since master"
summary: >
  6 months and 25 days is a long time to wait for another Git tip, but this one’s worth it, especially if you frequently need to tidy up feature branches before merging back into master.
related:
  - /post/git-log-master/
  - /post/git-set-upstream/
---

When I was new to Git, I saw editing the commit history as some sort of heinous sin. Git was about recording the exact steps that it took to create a piece of software, and to edit, resequence, or “squash” those steps was deceptive and dangerous.

Ah, youth.

Now that I more frequently end up reviewing other colleagues’ pull requests, or stepping through commits in poorly-documented open source modules, I’ve come to appreciate the value of some Git history housekeeping. Software development is messy. Tidying up your path to a solution, if it helps other people understand your working, can surely be no bad thing.

<a href="https://www.xkcd.com/1296/">
{% img "xkcd comic about git commit messages" "/media/xkcd-git-commit.jpg" %}
</a>

The workhorse of most Git history editing is `git rebase -i` – which shows you a list of all commits in a certain tree, and lets you reword, edit, resequence, or combine them.

All the work we do at [mySociety](https://mysociety.org) is constructed in feature branches, until it has been reviewed and merged into `master`. It’s not uncommon for me to want to quickly squash or resequence commits in a feature branch before it goes up for review.

If you know how many commits back you want to start your rebase, you can specify it:

```sh
git rebase -i HEAD~3
```

But keeping track of how many commits back you need to start is a pain. Much easier to just say “rebase everything since this branch diverged from `master`”:

For this, I have an alias in my `~/.gitconfig` file, like this:

```
[alias]
  rebase-since-master = !git rebase -i `git merge-base HEAD master`
```

Which can be run from the command line like this:

```sh
git rebase-since-master
```

Git works out the exact point at which the current feature branch (`HEAD`) diverged from `master`, and presents an overview of all the commits, ready for editing. It’s a super handy way to check your commits are nice and tidy before pushing to Github for code review.
