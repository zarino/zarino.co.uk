<?php /* Homepage, showing most recent post in full, and links to previous ones */

require_once('functions.php');

$posts = new PostList();

if(isset($_GET['slug'])){
    $post = new Post($_GET['slug']);
} else {
    $post = $posts->newest();
}

if($post->exists){
    include('templates/single-post.php');
} else {
    header("HTTP/1.0 404 Not Found");
    include('templates/404.php');
}

?>