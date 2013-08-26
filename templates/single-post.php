<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php print $post->title; ?> | blog.zarino.co.uk</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/zarino.css">
    <link rel="stylesheet" href="/css/blog.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Zarino Zappia</h1>
        </div>
        <nav class="container">
            <ul><?php print table_of_contents($posts); ?></ul>
        </nav>
    </header>
    <div id="content">
        <div class="container">
            <?php print date('Y-m-d H:i:s', $post->date); ?>
            <?php print $post->html; ?>
        </div>
    </div>
</body>
</html>