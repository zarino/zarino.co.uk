<?php /* Homepage, showing most recent post in full, and links to previous ones */

require_once('functions.php');

$posts = new PostList();

if(isset($_GET['slug'])){
    $post = $posts->find($_GET['slug']);
} else {
    $tmp = $posts->newest(3);
    $post = $tmp[0];
    $other_posts = array($tmp[1], $tmp[2]);
}

if($post->exists){
    include('templates/single-post.php');
} else {
    header("HTTP/1.0 404 Not Found");
    include('templates/404.php');
}

?>
