<?php
global $CFG;

require_once($CFG->libdir.'/ddllib.php');
require_once($CFG->libdir .'/simpletestlib/unit_tester.php');
require_once(dirname(__FILE__).'/../../../lib/filelib.php');
require_once(dirname(__FILE__).'/../../../lib/simpletestlib.php');
require_once(dirname(__FILE__).'/../system/feed_system.php');

// Define folder - negative course id
define('FOLDER', -101);

class newsfeed_test_base extends UnitTestCase {
    
    private $beforeprefix,$beforedataroot;
    protected $sys;
    
    const TESTPREFIX='utest_';
    const DATAROOTSUFFIX='/unittest';
    
    function setUp() {
        // Hack to clear context cache mucked up by unit tests
        // reset static course cache - it might have incorrect cached data
        global $context_cache, $context_cache_id;
        $context_cache    = array();
        $context_cache_id = array();
        
        // Set db prefix and dataroot for testing
        global $CFG, $db;
        $this->beforeprefix=$CFG->prefix;
        $this->beforedataroot=$CFG->dataroot;
        $CFG->prefix=self::TESTPREFIX;
        $CFG->dataroot=$this->beforedataroot.self::DATAROOTSUFFIX;
        
        $this->wipe_test_data();
        mkdir($CFG->dataroot);
        
        // Trash any existing tables and install
        $this->delete_tables();
        $this->sys=new feed_system;
        ob_start();
        install_from_xmldb_file(dirname(__FILE__).'/../db/install.xml');
        ob_end_clean();
        make_test_tables_like_real_one(array(
            'context', 'capabilities', 'role', 'role_capabilities', 'role_assignments', 'user_lastaccess','context_temp',
            'course','cache_flags','forum','forum_subscriptions', 'block_instance', 'block', 'course_categories',
            'events_handlers'),
            $this->beforeprefix, self::TESTPREFIX, $db);
        load_test_data(self::TESTPREFIX.'capabilities',array(
            array('id', 'name', 'captype', 'contextlevel', 'component'),
            array(1, 'block/newsfeed:approve', 'read', 10, 'block/newsfeed'),
            array(2, 'block/newsfeed:post', 'write', 10, 'block/newsfeed'),
            array(3, 'moodle/legacy:guest', 'read', 10, 'block/newsfeed')
            ),$db);
        load_test_data(self::TESTPREFIX.'role',array(
            array('id', 'name', 'description', 'sortorder', 'shortname'),
            array(1, 'Newsfeed approver', 'test approver', 0, 'newsfeedapprover'),
            array(2, 'Newsfeed poster', 'test poster', 1, 'newsfeedposter')
            ),$db);
        load_test_data(self::TESTPREFIX.'role_capabilities',array(
            array('id', 'contextid', 'roleid', 'capability', 'permission'),
            array(1, 1, 1, 'block/newsfeed:approve', 1),
            array(2, 1, 2, 'block/newsfeed:post', 1)
            ),$db);
        $this->add_block_instances();
        $this->make_fake_users();
        $this->sys->install_database();
            
        global $USER;
        if(!isset($USER->id)) {
            $USER->id=0;
        }
    }
    
    private function wipe_test_data() {
        $folder=$this->beforedataroot.self::DATAROOTSUFFIX;
        clearstatcache();
        if(is_dir($folder)) {
            fulldelete($folder);
        }
    }
    
    private function delete_tables() {
        global $db;
        wipe_tables(self::TESTPREFIX, $db);
        wipe_sequences(self::TESTPREFIX, $db);
    }
    
    protected function make_fake_users() {
        // Create fake users table
        load_test_table(self::TESTPREFIX.'user',array(
            array('id', 'username', 'firstname', 'lastname', 'email', 'deleted', 'lastaccess'),
            array(1,    'u1',       'user',      'one',      'u1@example.com', 0, 0),
            array(2,    'u2',       'user',      'two',      'u2@example.com', 0, 0),
            array(3,    'u3',       'user',      'three',    'u3@example.com', 0, 0)
            ));
    }
    
    protected function add_block_instances() {
        // Add newsfeed course category
        load_test_data(self::TESTPREFIX.'course_categories',array(
            array('id'),
            array(11)
            ));

        // Add newsfeed course
        load_test_data(self::TESTPREFIX.'course',array(
            array('id', 'category', 'shortname', 'startdate'),
            array(101, 11, 'NF001-06K', strtotime('1 June 2006'))
            ));

        // Add newsfeed block
        load_test_data(self::TESTPREFIX.'block',array(
            array('id', 'name', 'multiple'),
            array(1001, 'newsfeed', 1)
            ));

        // Add block instances
        load_test_data(self::TESTPREFIX.'block_instance',array(
            array('id', 'blockid', 'pageid', 'pagetype'),
            array(1, 1001, 101, 'course-view'),
            array(2, 1001, 101, 'course-view'),
            array(3, 1001, 101, 'course-view'),
            array(4, 1001, 101, 'course-view'),
            array(5, 1001, 101, 'course-view'),
            array(6, 1001, 101, 'course-view'),
            array(7, 1001, 101, 'course-view'),
            array(8, 1001, 101, 'course-view'),
            array(9, 1001, 101, 'course-view')
            ));
    }

    function tearDown() {
        // Wipe data
        $this->delete_tables();
        $this->wipe_test_data();
        
        // Put prefix back
        global $CFG;
        $CFG->prefix=$this->beforeprefix;
        $CFG->dataroot=$this->beforedataroot;

        // Hack to clear context cache mucked up by unit tests
        global $context_cache;
        $context_cache = array();
    }    
}
?>