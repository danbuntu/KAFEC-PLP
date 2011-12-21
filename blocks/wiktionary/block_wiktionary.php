<?php

// require the inbuilt moodle magpie copy
require_once($CFG->dirroot . '/lib/magpie/rss_fetch.inc');
// cache setting options
define('MAGPIE_CACHE_DIR', $CFG->dirroot . '/blocks/wiktionary/cache');
define('MAGPIE_CACHE_ON', 1);
//set cache age 1 one hour
define('MAGPIE_CACHE_AGE', 3600);

// debug stuff to output the whole rss feed for checking
//$url = "http://dictionary.reference.com/wordoftheday/wotd.rss";
//$rss = fetch_rss ($url);
//print_r($rss);
//echo "channel title: " . $rss->channel['title'] . "</p>";
//$channel_title = $rss->channel['title'];
//echo $channel_title;

class block_wiktionary extends block_base {
  function init() {
    $this->title   = get_string('title', 'block_wiktionary');
    $this->version = 2010030400;
  }

 // function instance_allow_config() {
 // return true;
// }

function instance_allow_multiple() {
  return false;
}

function hide_header() {
  return false;
}

  function get_content() {
    if ($this->content !== NULL) {
      return $this->content;
    }

//set the url
$url = "http://toolserver.org/~enwikt/wotd/";
// fetch the the url into a string to use it
$rss = fetch_rss ($url);
//feed only the first part of the array into a string for
$title = $rss->items[0] ['title'];
$description = $rss->items[0]['description'];
$link = $rss->items[0]['link'];
//spilt the result at ':' to remove the word
list ($word3, $title) = split(":",$title);
// uppercase the first letter

$title = ucfirst($title);




        $this->content->text  .=  '<div style="text-align: center";><b>' . ucfirst($title) . '</b><br />';
 	$this->content->text  .= '<div= worddesc>' . $description . '</div>';
        $this->content->text  .= '<div style="text-align: center;"><small><a rel="friend " target="_blank" href="' . $link . '">More Information</a></small>';
        $this->content->text  .= '<div style="text-align: center;"><small><small><a rel="friend " target="_blank" href="http://en.wiktionary.org">Powered by Wiktionary.org</a></small></small></div></div>';

    return $this->content;
  }
}

?>
