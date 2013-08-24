<?php

date_default_timezone_set('Europe/London');

class Post {

    public $exists = False;

    public $content = Null;
    public $slug = Null;
    public $path = Null;
    public $date = Null;

    public function __construct($slug) {
        if(file_exists('posts/' . $slug . '.md')){
            $this->exists = True;
            $this->slug = $slug;
            $this->path = 'posts/' . $slug . '.md';
            $this->date = filemtime($this->path);
            $this->content = file_get_contents($this->path);
        }
    }

}

function get_latest_post(){
    $files = scandir('posts', 1);
    $newest_file = $files[0];
    $slug = str_replace('.md', '', $newest_file);
    return new Post($slug);
}

?>