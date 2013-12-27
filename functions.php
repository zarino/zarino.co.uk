<?php

date_default_timezone_set('Europe/London');


require_once('vendor/php-markdown/Michelf/MarkdownInterface.php');
require_once('vendor/php-markdown/Michelf/Markdown.php');
require_once('vendor/php-markdown/Michelf/MarkdownExtra.php');
use \Michelf\MarkdownExtra;

class Post {

    public $exists = False;

    public $slug = Null;
    public $path = Null;
    public $date = Null;
    public $raw = Null;
    public $html = Null;
    public $title = Null;

    public function __construct($slug) {
        if(file_exists('posts/' . $slug . '.md')){
            $this->exists = True;
            $this->slug = $slug;
            $this->path = 'posts/' . $slug . '.md';
            $this->date = $this->get_date();
            $this->raw = file_get_contents($this->path);
            $this->html = MarkdownExtra::defaultTransform($this->raw);
            $this->title = $this->get_title_from_html($this->html);
        }
    }

    private function get_date() {
        preg_match('@^posts/(\d{4})-(\d{2})-(\d{2})@', $this->path, $matches);
        if(count($matches) == 4){
            return mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
        } else {
            return filemtime($this->path);
        }
    }

    private function get_title_from_html($html) {
        preg_match('@<h1[^>]*>(.+)</h1>@', $html, $matches);
        if(count($matches) == 2){
            return $matches[1];
        } else {
            return 'Untitled';
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

function table_of_contents($posts, $post=null){
    foreach($posts->all() as $p) {
        $date = date('jS F', $p->date);
        if(!is_null($post) && $post->slug == $p->slug){
            $class = ' class="active"';
        } else {
            $class = '';
        }
        print '<li><a href="/post/' . $p->slug . '"' . $class . '><strong>' . $p->title . '</strong> <span>' . $date . '</span></a></li>';
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
