<?php

// This script logs a Google Analytics pageview
// using the supplied $_GET variable, and then
// returns a 1 pixel transparent gif image.

// It's included in RSS feed items,
// to track pageviews.

require_once('functions.php');

$slug = $_GET['slug'];

if($enable_google_analytics && isset($slug)){
    /* Manually call the Google Analytics collection endpoint with cURL */
    $ch = curl_init();
    $data = array(
        'v'=> 1,
        'tid'=> $google_analytics_id,
        'cid'=> '0ddb7087-8ec9-4713-be77-40c4c5d83646',
        't'=> 'pageview',
        'dl'=> 'http://zarino.co.uk/feed/' . $slug,
        'aip'=> 1
    );
    curl_setopt($ch, CURLOPT_URL, 'http://www.google-analytics.com/collect?' . http_build_query($data));
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);
    curl_close($ch);
}

// generate 1px transparent gif
$data = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
$img = imagecreatefromstring($data);

// return the gif
header('Content-Type: image/gif');
imagegif($img);

// clear up
imagedestroy($img);

?>
