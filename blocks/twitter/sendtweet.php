<?php

require_once("../../config.php");
require_once 'twitter.class.php';

global $USER, $COURSE, $CFG;
//define variables
if($_POST['theme']) {
    $theme=$_POST['theme'];
}else {
    $theme=$CFG->theme;
}
if($_POST['location']) {
    $location=$_POST['location'];
}else {
    $location=$CFG->wwwroot.'/';
}

// Define strings passed through as hidden varibles
$context = $_POST['context'];
$message = $_POST['text'];
$id = $_POST['id'];
$twitterusername = $_POST['twitterusername'];
$twitterpassword = $_POST['twitterpassword'];
//set a count varible to 0 to check if the page has been sent before

$count = 0;
//
//echo $twitterpassword . " " . $twitterpassword;
// query the database
$query2 = "SELECT u.id, u.firstname, u.lastname, ud.data FROM {$CFG->prefix}role_assignments a Join {$CFG->prefix}user u on a.userid=u.id Join {$CFG->prefix}user_info_data ud on u.id=ud.userid Join {$CFG->prefix}user_info_field fe on ud.fieldid=fe.id  where contextid =  $context and ud.data IS NOT NULL and shortname = 'TwitterUsername'";
// Assign query results to a string
$names2 = get_records_sql($query2);
// print names array for debugging
// print_r($names2);
echo '<h2>';
print_string('sending', 'block_twitter');
echo '</h2>';
echo '<b>';
print_string('refresh', 'block_twitter');
echo '</b>';
echo '<br />';
echo '<br />';
print_string('following', 'block_twitter');
echo '<br />';
//cycle through the query and build the tweet string
// tweetstring is: d twittername message
foreach ($names2 as $names2) {
    $firstname = $names2->firstname;
    $lastname = $names2->lastname;
    $twitterid = $names2->data;
    $d = "d ";
    $space = " ";
    //   echo $firstname . " " . $lastname . " " . $twitterid . '<br />';
    //
//build the string 'tweet' to pass into the tweet function
    $tweet =  $d . $names2->data . $space . $message;
    $count = $count +1;
    echo 'Message #:' . $count . " ";
// Call the sendtweet function
    sendtweet ($tweet);
}

function sendtweet($tweet) {
    $tweet = $tweet;
    // print out the messages that have been sent
    echo '"' . $tweet . '"' . " --- Send status: ";
//send the tweet - can be commented out to allow debugging
  $twitter = new Twitter($twitterusername, $twitterpassword);
   $status = $twitter->send($tweet);
    echo 'Status: ' . $status ? 'OK' : 'ERROR' ;
    echo '<br />';
}

echo '<br />';
echo '<br />';
// message to the user to let them know everything is fine and a button to return them to their course
print_string('hurrah', 'block_twitter');
echo '<br />';
print_string('return', 'block_twitter');
echo '<br />';
echo '<form name="back" action="sendtweet.php" method="POST">';
echo '<input type="hidden" name="id" value="' . $id. '">';
print_string('return_button', 'block_twitter');
echo '<br />';
echo '<input type="submit" value="back" name="back" />';
echo '</form>';

//If the return button has been pressed automatically return to user to the course they came from
if (isset ($_POST['back'])) {
//echo $CFG->wwwroot;
//get the hiddent course id from the form
    $id = $_POST['id'];
//echo $id;
//build the url
    $url =  $CFG->wwwroot . '/course/view.php?id=' . $id;
//echo $url;
//redirect to the course the usre came from
    redirect($url);
}


?>
