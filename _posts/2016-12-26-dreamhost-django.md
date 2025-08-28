---
layout: post
title: Setting up a Django project on Dreamhost shared hosting
summary: >
  Fancy running Python-based frameworks like Django on your Dreamhost shared hosting? The Dreamhost docs are pretty out of date, but I’ve got you sorted.
related:
  - /post/git-push-to-deploy/
  - /post/git-rebase-since-master/
---

I’ve been with Dreamhost for years, and their basic shared hosting is surprisingly capable. But their [documentation for getting Python projects running](https://help.dreamhost.com/hc/en-us/sections/203851738-Python-Frameworks-SWIG) is really out of date.

So here are the notes I kept when I set up a new Django project on my Dreamhost shared hosting account earlier this week. Maybe they might help someone else in the future!

If you want to follow along, you’ll need to be fairly comfortable with the terminal / command line, Python, and Django.

---

## Create a domain and a database via the Dreamhost admin panel

1. Log into [panel.dreamhost.com](https://panel.dreamhost.com)
1. Go to [Domains > Manage Domains](https://panel.dreamhost.com/index.cgi?tree=domain.manage) and either add a new domain, or add hosting to an existing domain:
    - For "Run this domain under the user", opt to create a new user
    - Tick the "Remove WWW" checkbox
    - Tick the "HTTPS" checkbox
    - Tick the "Passenger" checkbox

1. Once the domain and user have been created, make sure to take a note of the user’s randomly-generated password – you’ll need to enter it when SSH’ing in for the first time.
1. Go to [Users > Manage Users](https://panel.dreamhost.com/index.cgi?tree=users.users), press the "Edit" button next to the user that was just created, and then:
    - Tick the "Shell user" checkbox
    - Tick the "Disallow FTP" checkbox

1. Go to [Goodies > MySQL](https://panel.dreamhost.com/index.cgi?tree=goodies.mysql) and create a new MySQL database:
    - For the "First User", opt to create a new user
    - Make a note of the password you pick – you’ll need to add it to your Django project’s `settings.py` later

---

## SSH into the new domain

If I were doing this setup from a Linux PC, I’d be able to run `ssh-copy-id`. But I’m on a Mac, so I have to copy and paste my key into `authorized_keys` by hand:

1. Open up a terminal, and copy your public key to the clipboard:

   ```sh
   pbcopy < ~/.ssh/id_rsa.pub
   ```

1. Then SSH in as the new user:

   ```sh
   ssh newuser@myproject.example.com
   ```

1. Now on the Dreamhost server, set up passwordless login for next time:

   ```sh
   mkdir .ssh
   chmod 700 .ssh
   touch .ssh/authorized_keys
   chmod 600 .ssh/authorized_keys
   nano .ssh/authorized_keys
   ```

1. Paste in the public key you copied, then save and exit (`ctrl-O, ctrl-W`).
1. End the SSH session (`ctrl-D`) and SSH back in, to check the passwordless login works.

---

## Set up a new Python virtualenv

1. Once you’ve SSHed into the server, create a virtualenv (imaginatively named "env" here):

   ```sh
   cd ~/myproject.example.com/
   virtualenv env
   ```

1. Activate the virtualenv, and install the django and mysql-python packages:

   ```sh
   cd ~/myproject.example.com/
   . env/bin/activate
   pip install django mysql-python
   ```

    Eventually, you’ll probably want to include these packages in a `requirements.txt` file that gets pip-installed automatically into the virtual environment by whatever deployment system you decide to use.

---

## Set up a new a Django project

1. Create a new Django project (named “myproject” here):

   ```sh
   django-admin startproject myproject
   ```

1. Edit `my-project/my-project/settings.py` to and set the database details:

   ```python
   DATABASES = {
       'default': {
           'ENGINE': 'django.db.backends.mysql',
           'NAME': 'my_dreamhost_database_name',
           'USER': 'my_dreamhost_database_user',
           'PASSWORD': 'my_dreamhost_database_password',
           'HOST': 'mysql.example.com',
           'PORT': 3306,
       }
   }
   ```

1. Add this after the `STATIC_URL` line at the end of the file:

   ```python
   STATIC_ROOT = os.path.dirname(BASE_DIR) + '/public/static/'
   ```

1. Create a `static` directory:

   ```sh
   mkdir -p public/static
   ```

1. Finish setting up your Django project:

   ```sh
   python myproject/manage.py collectstatic
   python myproject/manage.py migrate
   python myproject/manage.py createsuperuser
   ```

---

## Tell Dreamhost to serve your Django project via WSGI

1. Create a `passenger_wsgi.py` file that runs your Django application when visitors request pages at your domain.

   ```sh
   nano ~/myproject.example.com/passenger_wsgi.py
   ```

    And put this into it:

   ```python
   import os
   import sys

   cwd = os.getcwd()
   env_dir = os.path.join(cwd, 'env')
   project_dir = os.path.join(cwd, 'myproject')

   # Use the python executable from inside our virtualenv
   # https://help.dreamhost.com/hc/en-us/articles/215769548
   INTERP = os.path.join(env_dir, 'bin', 'python')
   if sys.executable != INTERP:
       os.execl(INTERP, INTERP, *sys.argv)

   # Add virtualenv packages to the start of the path
   sys.path.insert(0, os.path.join(env_dir, 'bin'))
   sys.path.insert(0, os.path.join(env_dir, 'lib', 'python2.7', 'site-packages'))
   sys.path.insert(0, os.path.join(env_dir, 'lib', 'python2.7', 'site-packages', 'django'))

   # Add brickwatch django project to the *end* of the path
   # (so it will be checked last).
   sys.path.append(project_dir)

   # Set environment variables for django to use
   os.environ['DJANGO_SETTINGS_MODULE'] = 'myproject.settings'

   from django.core.wsgi import get_wsgi_application
   application = get_wsgi_application()
   ```

1. Set up a `restart.txt` file (Dreamhost’s approved way of restarting the Passenger process that routes requests to your Django project):

   ```sh
   mkdir ~/myproject.example.com/tmp
   touch ~/myproject.example.com/tmp/restart.txt
   ```

1. When you visit `myproject.example.com` in a web browser you should see an _"It worked! Congratulations on your first Django-powered page."_ message from Django.
1. Visit `myproject.example.com/admin` and check that the page looks right. If the page is missing its CSS styles, then your `STATIC_ROOT` and/or `STATIC_URL` haven’t been set up properly.
1. Remember, if you make any changes to `passenger_wsgi.py`, you need to `touch tmp/restart.txt` (as above) to notify the Passenger process that it needs to load your new settings.

---

## Appendix: Helpful links

* [Dreamhost help: Python Frameworks & SWIG](https://help.dreamhost.com/hc/en-us/sections/203851738-Python-Frameworks-SWIG)
* [Dreamhost help: Create a DJango project using virtualenv](https://help.dreamhost.com/hc/en-us/articles/215319648-How-to-create-a-Django-project-using-virtualenv)
* [Installing Python 3 and Django on Dreamhost](http://blog.mattwoodward.com/2016/06/installing-python-3-and-django-on.html)
