<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php print $post->title; ?> | blog.zarino.co.uk</title>
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
          <p class="date"><?php print date('l jS F Y', $post->date); ?></p>
          <div class="post-content"><?php print $post->html; ?></div>
        </div>
    </div>
</body>
</html>
