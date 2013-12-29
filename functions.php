<?php

date_default_timezone_set('Europe/London');


require_once('vendor/php-markdown/Michelf/MarkdownInterface.php');
require_once('vendor/php-markdown/Michelf/Markdown.php');
require_once('vendor/php-markdown/Michelf/MarkdownExtra.php');
use \Michelf\MarkdownExtra;

class Post {

    // This class expects to be passed a filename, eg:
    // 2013-12-13-some-lovely-post.md
    // Which it will load (from the /posts directory)
    // and extract information from.

    public $exists = False;

    public $filename = Null;
    public $slug = Null;
    public $path = Null;
    public $date = Null;
    public $raw = Null;
    public $html = Null;
    public $title = Null;

    public function __construct($filename) {
        if(file_exists('posts/' . $filename)){
            $this->exists = True;
            $this->filename = $filename;
            $this->slug = $this->get_slug();
            $this->path = 'posts/' . $filename;
            $this->date = $this->get_date();
            $this->raw = file_get_contents($this->path);
            $this->html = MarkdownExtra::defaultTransform($this->raw);
            $this->title = $this->get_title_from_html($this->html);
        }
    }

    private function get_slug() {
        $fn = $this->filename;
        preg_match('@\d{4}-\d{2}-\d{2}-(.*)@', $fn, $matches);
        if(count($matches) == 2){
            $fn = $matches[1];
        }
        return str_replace('.md', '', $fn);
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
        foreach ($files as $filename) {
            if(!begins_with($filename, '.')){
                $this->posts[] = new Post($filename);
            }
        }
        function compare($post_a, $post_b) {
            // Sort by date, newest to oldest
            return $post_b->date - $post_a->date;
        }
        usort($this->posts, 'compare');
    }

    public function newest() {
        return $this->posts[0];
    }

    public function all() {
        return $this->posts;
    }

    public function find($slug) {
        // Since post filenames usually don't match their URL slugs
        // (filenames usually also include a date and file extension),
        // this function finds the post with the specified slug.
        foreach ($this->posts as $post) {
            if($post->slug == $slug){
                return $post;
            }
        }
        // Post wasn't found!
        return new Post($slug);
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
