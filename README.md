**zarino.co.uk:** a personal blog

Uses Jekyll to compile Markdown posts and SCSS styles into a static site.

Jekyll compiles the site into whatever directory Apache is expecting
(see `_git-post-receive.example.sh`) then Apache serves it, obeying the
`.htaccess` file as normal.
