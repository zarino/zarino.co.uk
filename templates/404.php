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
    <link rel="alternate" type="application/rss+xml" title="Zarino’s Blog" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/feed">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/zarino.css">
    <link rel="stylesheet" href="/css/blog.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="/js/jquery.ui.effect.min.js"></script>
    <script src="/js/blog.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/">Zarino Zappia</a></h1>
            <p id="subheading">develops things</p>
        </div>
        <nav class="container">
            <ul><?php print table_of_contents($posts, $post); ?></ul>
        </nav>
    </header>
    <div id="content">
        <div class="container">
            <h1>404!</h1>
        </div>
    </div>
    <footer>
        <div class="container">
            <img src="/img/zarinozappia.jpg" class="img-circle" width="256" height="256" alt="Zarino Zappia, wearing thick black and tortoise-shell glasses, a check shirt and a tweed Ted Baker jacket">
            <h3>Zarino Zappia <small title="MSc Social Science of the Internet, Oxford University">MSc</small></h3>
            <p class="lead">Designer, Coder &amp; Internetologist</p>
            <p>Zarino makes things that people want to use. He combines his intuition for design with a background in cultural studies and social science, to question, iterate, create and explain.</p>
            <p>Find him on <a href="https://twitter.com/zarino">Twitter</a> <a href="https://github.com/zarino">Github</a> and <a href="http://uk.linkedin.com/in/zarino">LinkedIn</a></p>
        </div>
    </footer>
</body>
</html>
