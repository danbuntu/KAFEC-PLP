<?php

// require the inbuilt moodle magpie copy
require_once($CFG->dirroot . '/lib/magpie/rss_fetch.inc');
// cache setting options
define('MAGPIE_CACHE_DIR', $CFG->dirroot . '/blocks/wordoftheday/cache');
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

class block_wordoftheday extends block_base {
  function init() {
    $this->title   = 'Word of The Day';
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
$url = "http://dictionary.reference.com/wordoftheday/wotd.rss";
// fetch the the url into a string to use it
$rss = fetch_rss ($url);
//feed only the first part of the array into a string for
$result = $rss->items[0]['description'];

//spilt the result at ':' to remove the word
list ($word, $desc) = split(":",$result);
// uppercase the first letter
$word = ucfirst($word);
// remove the trailing slashes from the description
$desc = substr($desc, 0, -1);
// uppercase the first letter
$desc = ucfirst($desc);

        $this->content->text  .=  '<div style="text-align: center";><b>' . $word . '</b><br />';
 	$this->content->text  .= '<div= worddesc>' . $desc . '</div>';
        $this->content->text  .= '<div style="text-align: center;"><a rel="friend " target="_blank" href="http://dictionary.reference.com/wordoftheday">More Information</a></div></div>';

    return $this->content;
  }
}

?>
