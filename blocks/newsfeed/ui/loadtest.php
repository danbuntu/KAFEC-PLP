<?php
/**
 * Test performance of key newsfeed features.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_once('sharedui.php');
require_once('../simpletest/roleutils.php');


define('UNITTEST',1); // Stops the newsfeed stuff trying to send emails

function time_start() {
    global $before;
    $before=microtime(true);
}

function time_report($name,$feedcount=0) {
    global $before;
    $time=sprintf('%01.3f',microtime(true)-$before);

    if($feedcount) {
        $perfeed=sprintf('%01.3f',$time/$feedcount);
        print "<tr><td>$name</td><td>$time</td><td>$perfeed</td></tr>";
    } else {
        print "<tr><td>$name</td><td>$time</td><td></td></tr>";
    }
}

function create_feed($name) {
    $tw=new transaction_wrapper();

    // Create actual feed
    $nf=new internal_news_feed();
    $nf->set_name($name);
    $newsfeedid=$nf->create_new();

    // Create roles, authids
    newsfeed_add_test_role($nf,2);
    newsfeed_add_test_role($nf,1,true);
    $nf->set_authids(null,array('one','two'));

    // Create messages in feed
    for($message=0;$message<10;$message++) {
        // Each message has 3 versions, 2 are approved
        $v=new news_entry_version;
        $v->set_title("Test message $message");
        $v->set_html("Test 1");
        $v->save_new(true,$newsfeedid);
        $v->set_html("Test 2");
        $v->save_new();
        $v->approve(1);
        $v->set_html("Test 3");
        $v->save_new();
    }

    $tw->commit();
}

do_admin_access_and_header('Load test');

define('TESTPREFIX','loadtest_');
define('SAMPLES',25);

set_time_limit(0);

// Set db prefix and dataroot for testing
global $CFG,$db;
$beforeprefix=$CFG->prefix;
$CFG->prefix=TESTPREFIX;

print '<h1>Newsfeed performance test</h1>';

$feedcounts=array(10,100,1000);//,10,100,1000,10000,25000);
foreach($feedcounts as $feedcount) {
    // Trash any existing tables and install afresh
    wipe_tables(TESTPREFIX, $db);
    wipe_sequences(TESTPREFIX, $db);
    feed_system::$inst->install_database();
    // Create fake users table
    load_test_table(TESTPREFIX.'user',array(
        array('id', 'username', 'firstname', 'lastname', 'email'),
        array(1,    'u1',       'user',      'one',      'u1@example.com'),
        array(2,    'u2',       'user',      'two',      'u2@example.com'),
        array(3,    'u3',       'user',      'three',    'u3@example.com')
        ));

    print <<<END
<h2>With $feedcount feeds</h2>
<table border='1' cellpadding="4">
<tr>
<td></td>
<th colspan="2">Time (s)</th>
</tr>
<tr>
<th>Task</th>
<th>Total</th>
<th>Per feed</th>
</tr>
END;

    time_start();
    for($i=0;$i<$feedcount;$i++) {
        create_feed("Test $i");
    }
    time_report('Create',$feedcount);
    time_start();
    db_do('VACUUM ANALYZE prefix_newsfeed');
    db_do('VACUUM ANALYZE prefix_newsfeed_includes');
    db_do('VACUUM ANALYZE prefix_newsfeed_external');
    db_do('VACUUM ANALYZE prefix_newsfeed_entries');
    db_do('VACUUM ANALYZE prefix_newsfeed_versions');
    db_do('VACUUM ANALYZE prefix_newsfeed_files');
    time_report('Analyze',$feedcount);

    $feeds=array();
    time_start();
    if($feedcount <= SAMPLES) {
        for($i=1;$i<=$feedcount;$i++) {
            $feeds[]=feed_system::$inst->get_feed($i);
        }
    } else {
        // Pick 25 random feeds to time
        $ids=array();
        for($i=1;$i<=$feedcount;$i++) {
            $ids[]=$i;
        }
        shuffle($ids);
        for($i=0;$i<SAMPLES;$i++) {
            $feeds[]=feed_system::$inst->get_feed($ids[$i]);
        }
        $feedcount=SAMPLES;
    }
    time_report('Get basic metadata',$feedcount);

    time_start();
    foreach($feeds as $nf) {
        $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
        has_capability('block/newsfeed:approve', $nfcontext);
    }
    time_report('Get roles',$feedcount);

    time_start();
    foreach($feeds as $nf) {
        $nf->get_optional_authids();
    }
    time_report('Get authids',$feedcount);

    time_start();
    foreach($feeds as $nf) {
        $nf->get_entries();
    }
    time_report('Get entries (approved)',$feedcount);

    time_start();
    foreach($feeds as $nf) {
        $nf->get_entries(false);
    }
    time_report('Get entries (all)',$feedcount);

    $tempfile="$CFG->dataroot/loadtest-newsfeed.xml";
    time_start();
    foreach($feeds as $nf) {
        $nf->build($tempfile);
    }
    unlink($tempfile);
    time_report('Build Atom data',$feedcount);

    print <<<END
</table>
END;
    flush();
}

// Wipe tables finally
wipe_tables(TESTPREFIX, $db);
wipe_sequences(TESTPREFIX, $db);

?>