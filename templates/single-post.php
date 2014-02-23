<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlentities($title, ENT_NOQUOTES, 'UTF-8'); ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="alternate" type="application/rss+xml" title="Zarino’s Blog" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/feed">
<?php if(isset($description)){ ?>
    <meta name="description" content="<?php echo htmlentities($description, ENT_QUOTES, 'UTF-8'); ?>">
<?php } ?>
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/zarino.css">
    <link rel="stylesheet" href="/css/blog.css">
    <script src="/js/jquery.min.js"></script>
    <script src="/js/jquery.ui.effect.min.js"></script>
    <script src="/js/jquery.scrolldepth.min.js"></script>
    <script src="/js/blog.js"></script>
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/">Zarino Zappia</a></h1>
            <p id="subheading">develops things</p>
        </div>
        <nav class="container">
            <ul><?php echo table_of_contents($posts, $post); ?></ul>
        </nav>
    </header>
    <div id="content">
        <div class="container">
          <p class="date">
              <a title="Permalink" href="<?php echo $post->url; ?>"><?php echo date('l jS F Y', $post->date); ?> <span class="muted">&infin;</span></a>
          </p>
          <p class="byline vcard">By <a class="fn" href="https://plus.google.com/u/0/105177279210927536682?rel=author">Zarino Zappia</span></p>
          <div class="post-content"><?php echo avoid_widows($post->html); ?></div>
        </div>
    </div>
    <?php if(isset($other_posts)){ ?>
    <div id="further-reading">
        <div class="container">
            <h2>Further reading:</h2>
            <?php foreach($other_posts as $other_post){ ?>
            <a href="<?php echo $other_post->url; ?>">
                <h3><?php echo avoid_widows($other_post->title); ?></h3>
                <p><?php echo date('l jS F Y', $other_post->date); ?></p>
            </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
    <footer>
        <div class="container vcard">
            <img src="/img/zarinozappia.jpg" class="img-circle photo" width="256" height="256" alt="Zarino Zappia, wearing thick black and tortoise-shell glasses, a check shirt and a tweed Ted Baker jacket">
            <h3><span class="fn">Zarino Zappia</span> <small title="MSc Social Science of the Internet, Oxford University">MSc</small></h3>
            <p class="lead title">Designer, Coder &amp; Internetologist</p>
            <p>Zarino makes things that people want to use. He combines his intuition for design with a background in cultural studies and social science, to question, iterate, create and explain.</p>
            <p>Find him on <a href="https://twitter.com/zarino">Twitter</a> <a href="https://github.com/zarino">Github</a> and <a href="http://uk.linkedin.com/in/zarino">LinkedIn</a></p>
        </div>
    </footer>
<?php include('analytics.php'); ?>
</body>
</html>
