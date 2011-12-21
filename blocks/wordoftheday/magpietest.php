<?php


require_once ('magpie/rss_fetch.inc');

$url ="http://dictionary.reference.com/wordoftheday/wotd.rss";
$rss = fetch_rss( $url );
print_r($rss);


?>