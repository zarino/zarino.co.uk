#!/bin/sh

BAREDIR="/home/zarinozappia/repos/blog.git"
GITDIR="/home/zarinozappia/gitdirs/zarino.co.uk"
APACHEDIR="/home/zarinozappia/zarino.co.uk"

unset GIT_DIR
export GIT_WORK_TREE=$GITDIR
mkdir -p $GITDIR

# Stay in the bare Git repo, but checkout the
# website files to $GIT_WORK_TREE (ie: $GITDIR).
git checkout -f

# Now go to the checked out files, and compile them
# into the place Apache expects.
cd $GITDIR
git --work-tree=$GITDIR --git-dir=$BAREDIR submodule update --init --recursive
jekyll build --destination $APACHEDIR