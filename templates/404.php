<?php

if(!isset($posts)){

  reauire_once('functions.php');
  $posts = new PostList();

}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>404 | blog.zarino.co.uk</title>
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
            <h1>404!</h1>
        </div>
    </div>
</body>
</html>