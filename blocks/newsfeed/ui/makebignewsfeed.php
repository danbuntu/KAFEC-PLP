<?php
/**
 * Creates a really big newsfeed database for performance testing.
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

do_admin_access_and_header('Make big newsfeed');

define('TESTPREFIX','bignf_');
define('FEEDCOUNT',286);
define('BLOCKSIZE',1);

set_time_limit(0);

// Set db prefix and dataroot for testing
global $CFG,$db;
$CFG->prefix=TESTPREFIX;

print '<h1>Creating '.FEEDCOUNT.' feeds...</h1>';

// Set up tables if for first time 
$tables = $db->MetaTables('TABLES', false, TESTPREFIX."%");
if(count($tables)==0) {
    print '<h2>Installing tables</h2>';
    flush();
    feed_system::$inst->install_database();
    // Create fake users table
    load_test_table(TESTPREFIX.'user',array(
        array('id', 'username', 'firstname', 'lastname', 'email'),
        array(1,    'u1',       'user',      'one',      'u1@example.com'),
        array(2,    'u2',       'user',      'two',      'u2@example.com'),
        array(3,    'u3',       'user',      'three',    'u3@example.com')
        ));
} else {
    print '<h2>Tables already installed</h2>';
}
print '<h2>Creating feeds</h2>';
flush();
$done=0;
while($done<FEEDCOUNT) {
    for($i=0;$i<BLOCKSIZE;$i++) {
        create_feed("Test $i on ".date('r'));
    }
    $done+=BLOCKSIZE;    
    print "<p>Done $done...</p>";
    flush();
}

print '<h2>Finished!</h2>';

?>