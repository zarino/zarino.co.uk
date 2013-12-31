<?php

require_once('functions.php');

if($enable_google_analytics){
    /* Manually call the Google Analytics collection endpoint with cURL */
    $ch = curl_init();
    $data = array(
        'v'=> 1,
        'tid'=> $google_analytics_id,
        'cid'=> '0ddb7087-8ec9-4713-be77-40c4c5d83646',
        't'=> 'pageview',
        'dl'=> 'http://zarino.co.uk/feed',
        'aip'=> 1
    );
    curl_setopt($ch, CURLOPT_URL, 'http://www.google-analytics.com/collect?' . http_build_query($data));
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);
    curl_close($ch);
}

/* Send correct XML header */
header("Content-Type: application/rss+xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';

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
