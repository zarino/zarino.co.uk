---
layout: post
title: Using Spotlight (or mdfind) from the terminal
summary: >
  A quick demo of how I use Spotlight on Mac OSX to search for words and phrases faster, and more easily, than Grep.
related:
  - /post/git-push-to-deploy/
  - /post/which-process-is-using-port/
---

If you’re a Mac developer, you’ve probably found yourself typing something like this quite a lot:

```sh
grep -r 'some search string' .
```

`Grep` searches the current folder (and subfolders, with `-r`) for a given search term. Which is awesome. But on large nested trees, it can be slow. I also have a devil of a time remembering what order the arguments go in (is it search term first, or directory first?).

Typical Mac users are accustomed to invoking Spotlight for lightning-fast searches of their hard drive. It turns out Spotlight has a command line client, called `mdfind`, but its arguments are just as gnarly as `grep`’s:

```sh
mdfind -onlyin ./ 'some search string'
```

So, I made a wrapper function, defined in my `~/.bash_profile` that gives me a nice simple `spotlight 'some search string'` interface for querying the current folder, recursively, and in a flash, with Spotlight:

```sh
# in ~/.bash_profile

spotlight() {
  if [ -z "$1" ]; then
    echo "Search the current folder for files containing a text string"
  else
    mdfind -onlyin ./ "$1";
  fi
}
```

Give it a try!

```sh
spotlight 'some search term'
```

It’s worth bearing in mind that, unlike `grep`, Spotlight does fuzzy searching. So “person” will match files containing `person`, `person-id`, `personality`, and `idPerson` (but not `impersonal`). Spotlight also ignores pretty much all punctuation, which can be a big gotcha when you’re searching for a PHP variable like `$foobar`—which will return no results—or even a hypenated string like `some-variable`.

In these cases, you can either second-guess Spotlight by rephrasing your search term without punctuation, or fall back to `grep`. And grab a cup of tea while it chugs away.
