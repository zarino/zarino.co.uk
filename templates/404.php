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
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/zarino.css">
    <link rel="stylesheet" href="/css/blog.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Site title goes here</h1>
        </div>
        <nav class="container">
            <ul><?php print table_of_contents($posts); ?></ul>
        </nav>
    </header>
    <div id="content">
        <div class="container">
            <h1>404!</h1>
        </div>
    </div>
</body>
</html>