<?php

header("Content-Type: application/rss+xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';

require_once('functions.php');
$posts = new PostList();

?>
<rss version="2.0">
<channel>
<title>Zarino’s Blog</title>
<link>http://zarino.co.uk</link>
<description>Zarino Zappia is a coder, designer and Internetologist. His blog covers everything from art to technology, javascript to typography.</description>
<language>en-gb</language>

<?php

foreach ($posts->all() as $post){
    echo '<item>
    <title>' . $post->title . '</title>
    <pubDate>' . date("D, d M Y H:i:s O", $post->date) . '</pubDate>
    <link>' . $post->url . '</link>
    <description>' . utf8_encode(htmlentities($post->html, ENT_COMPAT, 'utf-8')) . '
    </description>
</item>';
}

?>

</channel>
</rss>
