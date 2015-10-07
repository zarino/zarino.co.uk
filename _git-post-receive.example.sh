#!/bin/sh

# Assumes the git repository receiving the push is at
# ~/repos/zarino.co.uk, and the place Apache expects
# to serve the files from is ~/zarino.co.uk

GITDIR="/home/zarinozappia/repos/zarino.co.uk"
APACHEDIR="/home/zarinozappia/zarino.co.uk"

unset GIT_DIR
export GIT_WORK_TREE=$GITDIR
mkdir -p $GITDIR

# Stay in the blog.git bare repo, but checkout the
# website files to $GIT_WORK_TREE (ie: $GITDIR).
git checkout -f

# Now go to the checked out files, and compile them
# into the place Apache expects.
cd $GITDIR
jekyll build --destination $APACHEDIR
