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

class PostList {

    private $posts = array();

    public function __construct() {
        $files = scandir('posts', 1);
        $p = array();
        foreach ($files as $filename) {
            if(begins_with($filename, '.')){
                return;
            }
            $slug = str_replace('.md', '', $filename);
            $this->posts[] = new Post($slug);
        }
        print_r($this->posts);
    }

    public function newest() {
        return $this->posts[0];
    }

    public function all() {
        return $this->posts;
    }

}

function table_of_contents($posts){
    foreach($posts->all() as $p) {
        $slug = $p->slug;
        print '<li><a href="/post/' . $slug . '">' . $slug . '</a></li>';
    }
}

function begins_with($haystack, $needle) {
    return (strpos($haystack, $needle) === 0 ? True : False);
}

function contains($haystack, $needle) {
    return (strpos($haystack, $needle) ? True : False);
}

function ends_with($haystack, $needle) {
    return (strpos($haystack, $needle) === strlen($haystack)-strlen($needle) ? True : False);
}

?>