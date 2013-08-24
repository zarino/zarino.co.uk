<?php /* Homepage, showing most recent post in full, and links to previous ones */

require 'functions.php';

if(isset($_GET['slug'])){
    $post = new Post($_GET['slug']);
} else {
    $post = get_latest_post();
}

if(!$post->exists){
    print '404 Not Found';
}

# date( 'Y-m-d H:i:s', $post->date);

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
            
        </nav>
    </header>
    <div id="content">
        <div class="container">
            <?php print $post->content; ?>
        </div>
    </div>
</body>
</html>