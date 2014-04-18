<?php

/* Pretty much all page requests come through here.
   If a ?slug GET parameter has been set, we find the post.
   If not, we show the homepage. */

require_once('functions.php');

$posts = new PostList();

if(isset($_GET['slug'])){
    $post = $posts->find($_GET['slug']);
    if($post->exists){
        $title = $post->title . ' | Zarino Zappia';
        $other_posts = $post->get_related_posts();
        include('templates/single-post.php');
    } else {
        $title = '404 | Zarino Zappia';
        header("HTTP/1.0 404 Not Found");
        include('templates/404.php');
    }
} else {
    $tmp = $posts->newest(3);
    $post = $tmp[0];
    $other_posts = array($tmp[1], $tmp[2]);
    $title = 'Zarino Zappia | Designer, Coder & Internetologist';
    $description = 'Zarino Zappia is a designer, coder and Internetologist based in Liverpool, UK. This is his blog.';
    include('templates/single-post.php');
}

?>
