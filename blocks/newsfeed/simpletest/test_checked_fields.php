<?php

global $CFG;

require_once($CFG->libdir. '/simpletestlib/unit_tester.php');
require_once($CFG->libdir. '/simpletestlib/mock_objects.php');

require_once(dirname(__FILE__).'/../system/checked_fields.php');

class test_checked_fields extends UnitTestCase {
    
    function test_getset() {
        $cf=new checked_fields(array(
            'a' => array('//',1,'s')));
        
        // Missing fields
        try {
            $cf->set('unknown','');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_UNKNOWNFIELD);
        }
        
        // Get unset field
        $this->assertEqual($cf->get('a'),null);
        
        // Set and get field
        $cf->set('a','oo');
        $this->assertEqual($cf->get('a'),'oo');
    }
    
    function test_dirty() {
        $cf=new checked_fields(array(
            'a' => array('/x/',1,'s')));
        $this->assertEqual($cf->is_changed(),false);
        
        $cf->set('a','x');
        $this->assertEqual($cf->is_changed(),true);

        $cf->clear_changed();
        $this->assertEqual($cf->is_changed(),false);
                    
        // Dirty after exception
        try {
            $cf->set('a','');
            $this->assertTrue(false);
        } catch(Exception $e) {
        }
        $this->assertEqual($cf->is_changed(),false);
    }
    
    function test_restrictions() {
        $cf=new checked_fields(array(
            'regex' => array('/^[abc]$/',1,'s'),
            'nullor' => array('!NULLOR/^[abc]$/',2,'s'),
            'notnull' => array('!NOTNULL',3,'s'),
            'bool' => array('!BOOLEAN',4,'b'),
            'noblank' => array('!NOBLANK',5,'s'),
            'allowzero' => array('/^[0-9]+$/',6,'i'),
            ));
        
        try {
            $cf->set('regex','d');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),1);
        }
        $cf->set('regex','a');

        try {
            $cf->set('nullor','d');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),2);
        }
        $cf->set('nullor','a');
        $cf->set('nullor',null);
        
        try {
            $cf->set('notnull',null);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),3);
        }
        $cf->set('notnull','');
        $cf->set('notnull','frog');
        
        try {
            $cf->set('bool',null);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),4);
        }
        try {
            $cf->set('bool',0);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),4);
        }
        try {
            $cf->set('bool','');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),4);
        }
        $cf->set('bool',true);
        $cf->set('bool',false);
        
        try {
            $cf->set('noblank','');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),5);
        }
        $cf->set('noblank','frog');
        $cf->set('noblank',null);

        $cf->set('allowzero',0);
        
        // Now check that db methods apply check        
        $cf=new checked_fields(array(
            'a' => array('/x/',1,'s')));
        try {
            $cf->get_insert_strings();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),1);
        }
        try {
            $cf->get_update_string();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),1);
        }
        $cf->set('a','x');
        $cf->get_insert_strings();
        $cf->get_update_string();
    }
    
    function test_dbstrings() {
        // Invalid data types
        $cf=new checked_fields(array(
            's' => array('!NULLOR//',0,'x')));
        try {
            $cf->get_insert_strings();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_UNKNOWNTYPE);
        }
        
        // Valid types
        $cf=new checked_fields(array(
            's' => array('!NULLOR//',0,'s'),
            'i' => array('!NULLOR//',0,'i'),
            'b' => array('!NULLOR//',0,'b'),
            ));
        $this->assertEqual($cf->get_update_string(),
            "");
        $this->assertEqual($cf->get_insert_strings(),
            array('s,i,b',"NULL,NULL,NULL"));
        $cf->set('s','x');
        $cf->set('i',10);
        $cf->set('b',true);
        $this->assertEqual($cf->get_update_string(),
            "s='x',i=10,b=1");
        $this->assertEqual($cf->get_insert_strings(),
            array('s,i,b',"'x',10,1"));
        $cf->set('s','');
        $cf->set('i',0);
        $cf->set('b',false);
        
        $this->assertEqual($cf->get_update_string(),
            "s='',i=0,b=0");
        $this->assertEqual($cf->get_insert_strings(),
            array('s,i,b',"'',0,0"));
        $cf->set('s',null);
        $cf->set('i',null);
        $cf->set('b',null);
        $this->assertEqual($cf->get_update_string(),
            "s=NULL,i=NULL,b=NULL");
        $this->assertEqual($cf->get_insert_strings(),
            array('s,i,b',"NULL,NULL,NULL"));
        $cf->clear_changed();
        $cf->set('i','4');
        $this->assertEqual($cf->get_update_string(),
            "i=4");
        $this->assertEqual($cf->get_insert_strings(),
            array('s,i,b',"NULL,4,NULL"));
        
    }
    
    function test_set_from_db() {
        $cf=new checked_fields(array(
            'a' => array('/x/',1,'s')));
            
        // Set that does nothing
        $input=array();
        $cf->set_from_db($input);        
        $this->assertTrue(true);
        
        // Set that makes it invalid
        $input=array('a'=>'b');
        try {
            $cf->set_from_db($input);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),1);
        }
        $this->assertEqual($cf->get('a'),null);
        
        // Set that works
        $input=array('a'=>'x');
        $cf->set_from_db($input);
        $this->assertEqual($cf->get('a'),'x');
        
        // Binary set
        $cf=new checked_fields(array(
            'b' => array('!BOOLEAN',1,'b')));
        $input=array('b'=>7); // Invalid boolean
        try {
            $cf->set_from_db($input);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),1);
        }
        // Valid booleans (in database)
        $input=array('b'=>1);
        $cf->set_from_db($input);
        $input=array('b'=>0);
        $cf->set_from_db($input);            
    }
    
}
?>
