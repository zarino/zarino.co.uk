<?php /* Homepage, showing most recent post in full, and links to previous ones */

require_once 'functions.php';

$posts = new PostList();

if(isset($_GET['slug'])){
    $post = new Post($_GET['slug']);
} else {
    $post = $posts->newest();
}

if(!$post->exists){
    print '404 Not Found';
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>blog.zarino.co.uk</title>
</head>
<body>
    <header>
        <div class="container">
            <h1>Site title goes here</h1>
        </div>
        <nav class="container">
            <ul><?php foreach($posts->all() as $post) {
                    print '
                <li><a href="/post/' . $post->slug . '">' . $post->slug . '</a></li>';
                } ?>

            </ul>
        </nav>
    </header>
    <div id="content">
        <div class="container">
            <?php print $post->content; ?>
        </div>
    </div>
</body>
</html>