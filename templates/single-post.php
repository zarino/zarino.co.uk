<?php

require_once('vendor/Markdown.php');
use \Michelf\Markdown;

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
            <ul><?php print table_of_contents($posts); ?></ul>
        </nav>
    </header>
    <div id="content">
        <div class="container">
            <?php print date('Y-m-d H:i:s', $post->date); ?>
            <?php print Markdown::defaultTransform($post->content); ?>
        </div>
    </div>
</body>
</html>