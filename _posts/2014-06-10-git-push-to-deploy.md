---
layout: post
title: Deploying web projects with Git
summary: >
  How to set up Git and Sass on a typical low-cost web server (in this case Dreamhost) so you can just push your work remotely and have it automatically deployed and running in seconds.
related:
  - /post/ds214se-under-the-hood/
  - /post/write-hfs-synology-nas/
---

This blog, like most of the code I write, is version-controlled with Git. You can browse the source code on [my Github account](https://github.com/zarino/zarino.co.uk).

Recently, while working on a side-project, I discovered the awesomeness of not only versioning, but also deploying your code with Git. And the best part is, it works with any old server, as long as you’ve got SSH access.

![Illustration of Git remotes](/media/git-remotes.jpg)

I use Dreamhost for most of my personal projects, including this blog. And I’ve got it set up to [use my SSH key for login](http://wiki.dreamhost.com/SSH#Passwordless_Login), so I can just type `ssh <username>@zarino.co.uk` and I’m in.[^1]

[^1]: In fact, I’ve got my `~/.ssh/hosts` file set up so that I only have to type `ssh blog`. Google “SSH hosts alias” for more info.

With a little Git magic, I’m also able to just type `git push blog` and all my code gets deployed to the live site. Here’s how:

1. SSH into your web server and create a directory to store your Git repositories (you can call this directory anything, and put it anywhere, but `~/repos` is a sensible default):

       ssh <username>@zarino.co.uk
       mkdir repos

2. Make another directory inside that one, to store the remote Git repo for your project, and then `cd` into it (in this case, I’m calling it `blog.git`, you can name it what you like):

       mkdir repos/blog.git
       cd repos/blog.git

3. Create a “bare” Git repository[^2] in `repos/blog.git`, and create a new file at `hooks/post-receive` inside that repo:

       git init --bare
       nano hooks/post-receive

4. Paste the following code into the post-receive file, replacing `<path-to-web-directory>` with the full path to the place your code is served from (the default on Dreamhost is `/home/<username>/<domain-name>`):

       #!/bin/sh
       export GIT_WORK_TREE=<path-to-web-directory>
       git checkout -f

5. Make the post-receive hook executable:

       chmod +x hooks/post-receive

6. Now, on your local machine, add a new remote to your Git repo (in this case, I’ve called the new remote `blog` but you can call it whatever your like):

       git remote add blog ssh://<username>@<server>/~/repos/blog.git

7. Push to your new remote!

       git push blog

[^2]: Initializing a ‘bare’ repository avoids that annoying `receive.denyCurrentBranch` warning you get when you push to a remote repo.

All your local changes will be pushed to the remote repo, and your post-receive hook will check out the latest files into your web directory, ready to be served up to visitors.

The really cool thing is, you can add more stuff to the post-receive hook, and it’ll all get executed whenever you push. So, if you use Sass, for example, to compile your CSS, you can use a post-receive hook like this to compile your Sass files whenever you push:[^3]

[^3]: The `PATH`, `GEM_HOME`, and `GEM_PATH` stuff is a workaround to get the Sass compiler working on Dreamhost servers. By default, Dreamhost accounts don’t come with the Sass Ruby gem installed, so you have to [install it yourself](http://wiki.dreamhost.com/Gems) and then include these paths in your environment so that the `sass --update` command becomes available.

~~~
#!/bin/sh

# Dreamhost needs this to put Sass Ruby gem on the path
export PATH=/home/<username>/.gems/bin:/usr/lib/ruby/gems/1.8/bin/:$PATH
export GEM_HOME=/home/<username>/.gems
export GEM_PATH=/home/<username>/.gems:/usr/lib/ruby/gems/1.8

export WEB_DIR=/home/<username>/example.zarino.co.uk
export GIT_WORK_TREE=$WEB_DIR

git checkout -f

echo 'Compiling Sass...'
sass --update $WEB_DIR/sass:$WEB_DIR/css
~~~
