<?php

date_default_timezone_set('Europe/London');

$google_analytics_id = 'UA-18117917-1';
$enable_google_analytics = $_SERVER['SERVER_NAME'] == 'zarino.co.uk';

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
            $this->is_draft = False;
            $this->date = $this->get_date();
            $this->raw = file_get_contents($this->path);
            $this->html = $this->get_html();
            $this->body = $this->get_body();
            $this->preview = $this->get_preview();
            $this->title = $this->get_title_from_html($this->html);
            $this->url = 'http://' . $_SERVER['HTTP_HOST'] . '/post/' . $this->slug;
            $this->tracker = '<img src="http://' . $_SERVER['HTTP_HOST'] . '/tracker.gif?slug=' . $this->slug . '">';
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
            $this->is_draft = False;
            return mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
        } else {
            $this->is_draft = True;
            return filemtime($this->path);
        }
    }

    private function get_title_from_html($html) {
        preg_match('@<h1[^>]*>(.+)</h1>@', $html, $matches);
        if(count($matches) == 2){
            return $matches[1];
        } else {
            return '(' . $this->filename . ')';
        }
    }

    private function get_html() {
        // remove LINK elements from raw post markdown
        $markdown = preg_replace('@<link[^>]+href="/post/[^>]+"[^>]*>@', '', $this->raw);
        // remove LINK elements from raw post markdown
        $markdown = preg_replace('@<meta name="description"[^>]*>@', '', $markdown);
        // compile markdown into HTML
        return MarkdownExtra::defaultTransform($markdown);
    }

    private function get_body(){
        $html = $this->html;
        // remove first H1 from compiled post body
        $body = preg_replace('@<h1[^>]*>.*</h1>\s*@', '', $html, 1);
        // make relative URLs absolute
        $body = preg_replace('@(href|src)="/@', '$1="http://' . $_SERVER['HTTP_HOST'] . '/', $body);
        return $body;
    }

    public function get_preview(){
        $preview = null;
        preg_match('@<meta name="description" content="([^"]+)">@', $this->raw, $matches);
        if(count($matches) > 1){
            return $matches[1];
        } else {
            return null;
        }
    }

    public function get_related_posts(){
        $all_posts = new PostList();
        $related_posts = null;
        preg_match_all('@<link[^>]+href="/post/(?P<slug>[^>]+)"[^>]*>@', $this->raw, $matches);
        if(count($matches['slug']) > 0){
            $related_posts = array();
            foreach($matches['slug'] as $slug){
                $related_posts[] = $all_posts->find($slug);
            }
        }
        return $related_posts;
    }

    public function get_formatted_date($include_day) {
        $time_difference = time() - $this->date;
        $eleven_months = 28512000;
        $format = 'jS F';
        if($time_difference < 0 || $time_difference > $eleven_months){
            $format = $format . ' Y';
        }
        if($include_day){
            $format = 'l ' . $format;
        }
        return date($format, $this->date);
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
        usort($this->posts, function ($post_a, $post_b) {
            // Sort by date, newest to oldest
            return $post_b->date - $post_a->date;
        });
    }

    public function newest($number=1) {
        $tmp = array();
        for($i = 0; $i < $number; $i++){
            $tmp[] = $this->posts[$i];
        }
        return $tmp;
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
    $i = 1;
    foreach($posts->all() as $p) {
        $class_list = array();
        if($p->is_draft){
            $class_list[] = 'draft';
        }
        if(!is_null($post) && $post->slug == $p->slug){
            $class_list[] = 'active';
        }
        if($i > 10){
            $class_list[] = 'hidden';
        }
        if($i > 1 && $i % 10 == 1){
            print "\n" . '<li class="show-more"><a href="/">Show more</a></li>';
        }
        print "\n" . '<li class="' . implode(' ', $class_list) . '"><a href="/post/' . $p->slug . '"><strong>' . avoid_widows($p->title) . '</strong> <span>' . $p->get_formatted_date(False) . '</span></a></li>';
        $i += 1;
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

function avoid_widows($text){
    // replaces the last space character at
    // the end of $text with an &nbsp; entity.
    return preg_replace('@ ([^ ]+)$@', '&nbsp;$1', $text);
}

?>
