<?php

require_once('newsfeed_test_base.php');
require_once('roleutils.php');

class test_feeds extends newsfeed_test_base {
    
    private function assert_sql_count($table,$condition,$count) {
        $rs=$this->feeds->do_sql("SELECT COUNT(*) FROM ".self::TESTPREFIX."resourcepage_$table WHERE $condition");
        if(!$rs) throw new Exception('DB error: '.$db->ErrorMsg());
        $this->assertEqual($rs->fields[0],$count);        
    }
    
    private function assert_sql_column($query,$array) {
        $rs=$this->feeds->do_sql($query);
        if(!$rs) throw new Exception('DB error: '.$db->ErrorMsg());
        $output=array();
        while(!$rs->EOF) {
            $unsorted=array_values($rs->fields);
            $output[]=$unsorted[0];
            $rs->MoveNext();
        }
        $this->assertEqual($array,$output);
    }    
    
    private function assert_sql_row($query,$array) {
        $rs=$this->feeds->do_sql($query);
        if(!$rs) throw new Exception('DB error: '.$db->ErrorMsg());
        $output=array();
        foreach($rs->fields as $key=>$value) {
            if(preg_match('/[^0-9]/',$key)) {
                $output[]=$value;
            }
        }
        $this->assertEqual($array,$output);
    }    
    
    private function assert_sql_data($query,$array) {
        $rs=$this->feeds->do_sql($query);
        if(!$rs) throw new Exception('DB error: '.$db->ErrorMsg());
        $output=array();
        while(!$rs->EOF) {
            $row=array();
            $field=0;
            while(array_key_exists($field,$rs->fields)) {
                $row[]=$rs->fields[$field++];
            }
            $output[]=$row;
            $rs->MoveNext();
        }
        $this->assertEqual($array,$output);
    }    
    
    function test_feed_setup() {
        // Blank newsfeed
        $nf=new news_feed();
        $nf->set_blockinstance(1);
        try {
            $nf->get_id();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NOID);
        }
        
        // Folder
        $correct=-101; // Root
        $this->assertEqual(FOLDER,$nf->get_folder()->get_id());
        
        // Name
        try {
            $nf->set_name('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NAMEINVALID);
        }
        try {
            $nf->set_name('not/allowed');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NAMEINVALID);
        }
        $correct='NAME';
        $nf->set_name($correct);
        $this->assertEqual($correct,$nf->get_name());
        
        // Pres
        $correct='06K';
        $this->assertEqual($correct,$nf->get_pres());
        $this->assertEqual('NF001-06K NAME',$nf->get_full_name());
        
        // Date
        $correct=strtotime("1 June 2006");
        $this->assertEqual($correct,$nf->get_start_date());
        
        // Public
        try {
            $nf->set_public('3');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_PUBLICINVALID);
        }
        $correct=true;
        $nf->set_public($correct);
        $this->assertEqual($correct,$nf->is_public());
        
        // Default authid(s)
        try {
            $nf->set_default_authid('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_AUTHIDINVALID);
        }
        $correct='SAMSID';
        $nf->set_default_authid($correct);
        $this->assertEqual($correct,$nf->get_default_authid());
        $correct=null;
        $nf->set_default_authid($correct);
        $this->assertEqual($correct,$nf->get_default_authid());
        
        // Deleted
        $this->assertEqual(false,$nf->is_deleted());
        try {
            $nf->set_deleted('3');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_DELETEDINVALID);
        }
        $nf->set_deleted(true);
        $this->assertEqual(true,$nf->is_deleted());                
    }    
    
    function test_feed_createupdate() {
        $nf=new news_feed();
        $nf->set_blockinstance(1);
        // Must configure some variables before creating
        try {
            $nf->create_new();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NAMEINVALID);
        }
        $nf->set_name('Test feed');
        $this->assertEqual(1,$nf->create_new());
        $nf=new news_feed();
        $nf->set_blockinstance(2);
        $nf->set_name('Test feed 2');
        $this->assertEqual(2,$nf->create_new());
        
        $nf1=$this->sys->get_feed(1);
        $this->assertEqual(FOLDER,$nf1->get_folder()->get_id());
        $this->assertEqual('Test feed',$nf1->get_name());
        $nf2=$this->sys->get_feed(2);
        $this->assertEqual(FOLDER,$nf2->get_folder()->get_id());
        $this->assertEqual('Test feed 2',$nf2->get_name());

        $this->assertTrue(!$nf->save_changes());
        
        $nf->set_name('Test feed renamed');
        $this->assertTrue($nf->save_changes());
        
        $nf2=$this->sys->get_feed(2);
        $this->assertEqual('Test feed renamed',$nf2->get_name());
        $this->assertEqual('06K',$nf2->get_pres());
        
        $nf->set_name('Name 3');
        $nf->set_summary('Summary');
        $nf->set_public(false);
        $nf->set_default_authid('SAMSTHING');
        $nf->set_deleted(true);
        $this->assertTrue($nf->save_changes());
        
        $nf2=$this->sys->get_feed(2);
        // Folders are now irrelevant as use course categories
        //$this->assertEqual($anotherfolder->get_id(),$nf2->get_folder()->get_id());
        $this->assertEqual('Name 3',$nf2->get_name());
        $this->assertEqual('06K',$nf2->get_pres());
        $this->assertEqual('Summary',$nf2->get_summary());
        $this->assertEqual(strtotime('1 June 2006'),$nf2->get_start_date());
        $this->assertEqual(false,$nf2->is_public());
        $this->assertEqual('SAMSTHING',$nf2->get_default_authid());
        $this->assertEqual(true,$nf2->is_deleted());
        
        // Now is the dirty flag ok?
        $this->assertTrue(!$nf->save_changes());
        
        // Multiple feed tests:
        
        // None in request
        $this->assertEqual($this->sys->get_feeds(array()),array());        
        // Invalid IDs 
        try {
            $this->sys->get_feeds(array('frog','doughnut'));
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_INVALIDID);
        }
        // Nonexistent ID 
        try {
            $this->sys->get_feeds(array(1,2,666));
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_INVALIDID);
        }
        // Success
        $feeds=$this->sys->get_feeds(array(1,2));
        $this->assertEqual(count($feeds),2);
        $idlist[]=$feeds[0]->get_id();
        $idlist[]=$feeds[1]->get_id();
        $this->assertEqualSort($idlist,array(1,2));        
    }
    
    
    function test_externalfeed_createupdate() {
        $nf=new external_news_feed();
        $nf->set_blockinstance(1);
        $nf->set_name('Test feed');
        // Must configure some variables before creating
        try {
            $nf->create_new();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_URLINVALID);
        }
        try {
            $nf->set_url('http://www.whatever.com/');
            $nf->create_new();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_FREQINVALID);
        }
        
        // Set freq
        try {
            $nf->set_check_freq('something');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_FREQINVALID);
        }
        $nf->set_check_freq(60*60*24);
        
        // OK, test setting the url
        try {
            $nf->set_url('something');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_URLINVALID);
        }
        try {
            $nf->set_url(null);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_URLINVALID);
        }
        try {
            $nf->set_url('http://some thing/');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_URLINVALID);
        }
        $nf->set_url('https://something');
        $nf->set_url('http://something');
        
        // Right, URL is now set, go with creation
        $id=$nf->create_new(); 
            // ID of this is 2 under Postgres because it doesn't reuse
            // the sequence number that got rolled back in the above
            // failures.
        
        // Actually check the creation worked
        $nf1=$this->sys->get_feed($id);
        $this->assertEqual(FOLDER,$nf1->get_folder()->get_id());
        $this->assertEqual('Test feed',$nf1->get_name());
        $this->assertEqual('http://something',$nf1->get_url());
        $this->assertEqual(24*60*60,$nf1->get_check_freq());
        $this->assertEqual(null,$nf1->get_error());

        $this->assertTrue(!$nf->save_changes());
        
        // Check changes in other fields still work
        $nf->set_name('Test feed renamed');
        $this->assertTrue($nf->save_changes());

        // And in our fields
        $nf->set_url('http://new');
        $nf->set_error('Error!');
        $this->assertTrue($nf->save_changes());
        
        // Now is the dirty flag ok?
        $this->assertTrue(!$nf->save_changes());

        // Check changes took effect       
        $nf1=$this->sys->get_feed($id);
        $this->assertEqual(FOLDER,$nf1->get_folder()->get_id());
        $this->assertEqual('Test feed renamed',$nf1->get_name());
        $this->assertEqual('http://new',$nf1->get_url());
        $this->assertEqual('Error!',$nf1->get_error());
    }
    
    function test_roles() {                    
        // Create feed
        $nf=new internal_news_feed();
        $nf->set_blockinstance(1);
        $nf->set_name('Test feed');
        $nf->create_new();
        
        // Get feed users (should be none)
        $this->assertEqual($nf->get_feed_users(),array());
        
        // Add a poster and an approver
        newsfeed_add_test_role($nf,1);
        newsfeed_add_test_role($nf,3,true);

        // Should still be none because we haven't refreshed yet        
        $this->assertEqual($nf->get_feed_users(),array());
        
        // Refresh and check that we've got data
        $nf->refresh_roles();
        $roles=$nf->get_feed_users();
        $posters=$roles[internal_news_feed::ROLE_POSTER];
        $this->assertEqual(count($posters),1);
        
        // Check the data values
        $this->assertEqual($posters[0]->get_user_id(),1);
        $this->assertEqual($posters[0]->get_role_name(),internal_news_feed::ROLE_POSTER);
        $this->assertEqual($posters[0]->get_first_name(),'user');
        $this->assertEqual($posters[0]->get_last_name(),'one');
        $this->assertEqual($posters[0]->get_email(),'u1@example.com');

        // Sample check for approver
        $approvers=$roles[internal_news_feed::ROLE_APPROVER];
        $this->assertEqual(count($approvers),1);
        $this->assertEqual($approvers[0]->get_user_id(),3);
        
        // Check post capabilities
        $this->assertTrue(has_feed_capability(1, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users())); // Has role
        $this->assertTrue(!has_feed_capability(2, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users())); // Has no roles
        $this->assertTrue(!has_feed_capability(3, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users())); // Has other role
        $this->assertTrue(!has_feed_capability(1, internal_news_feed::ROLE_APPROVER, $nf->get_blockinstance(), $nf->get_feed_users()));
        $this->assertTrue(has_feed_capability(3, internal_news_feed::ROLE_APPROVER, $nf->get_blockinstance(), $nf->get_feed_users()));
        
        // Add another poster
        newsfeed_add_test_role($nf,2);
        $nf->refresh_roles();
        $this->assertTrue(has_feed_capability(1, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users()));
        $this->assertTrue(has_feed_capability(2, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users()));
        $this->assertTrue(has_feed_capability(3, internal_news_feed::ROLE_APPROVER, $nf->get_blockinstance(), $nf->get_feed_users()));
        // Remove first one
        newsfeed_remove_test_role($nf,1);
        $this->assertTrue(has_feed_capability(1, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users())); // Still true until refresh
        $nf->refresh_roles();
        $this->assertTrue(!has_feed_capability(1, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users()));
        
        // Make 2 a poster AND moderator
        newsfeed_add_test_role($nf,2,true);
        newsfeed_add_test_role($nf,1);
        $nf->refresh_roles();
        $this->assertTrue(has_feed_capability(2, internal_news_feed::ROLE_POSTER, $nf->get_blockinstance(), $nf->get_feed_users()));
        $this->assertTrue(has_feed_capability(3, internal_news_feed::ROLE_APPROVER, $nf->get_blockinstance(), $nf->get_feed_users()));
        
        $this->assertEqualSort($nf->get_poster_usernames(),array('u1','u2'));
    }
    
    function test_authids() {
        // Create feed and setup default authid
        $nf=new internal_news_feed();
        $nf->set_blockinstance(1);
        $nf->set_name('Test feed');
        $id=$nf->create_new();
        $nf->set_default_authid('FROG');
        
        // Try adding invalid authids
        try {
            $nf->add_optional_authid('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_AUTHIDINVALID);
        }
        try {
            $nf->add_optional_authid('FROG');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_ALREADYGOTAUTHID);
        }
        // Now a valid one
        $nf->add_optional_authid('TADPOLE');
        // And one more invalid
        try {
            $nf->add_optional_authid('TADPOLE');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_ALREADYGOTAUTHID);
        }
        // Now check list
        $nf->add_optional_authid('AMPHIBIAN');
        $nf->add_optional_authid('FROGSPAWN');
        $nf2=$this->sys->get_feed($id); // Getting new feed forces it to get from db
        $this->assertEqualSort(
            $nf2->get_optional_authids(),array('AMPHIBIAN','FROGSPAWN','TADPOLE'));
        
        // Test remove
        try {
            $nf->remove_optional_authid('FROG'); // No you can't remove default like this
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NOTGOTAUTHID);
        }
        $nf->remove_optional_authid('AMPHIBIAN');
        $nf2=$this->sys->get_feed($id);
        $this->assertEqualSort(
            $nf2->get_optional_authids(),array('FROGSPAWN','TADPOLE'));
        
        // Test set that overrides everything
        $nf->set_authids('ONE',array('TWO','THREE','FOUR'));
        $nf->save_changes();
        $nf2=$this->sys->get_feed($id); // Getting new feed forces it to get from db        
        $this->assertEqual($nf2->get_default_authid(),'ONE');
        $this->assertEqualSort($nf2->get_optional_authids(),
            array('TWO','THREE','FOUR'));

        // Test set that switches from default to normal
        $nf->set_authids('TWO',array('ONE','THREE','FOUR'));
        $nf->save_changes();
        $nf2=$this->sys->get_feed($id);        
        $this->assertEqual($nf2->get_default_authid(),'TWO');
        $this->assertEqualSort($nf2->get_optional_authids(),
            array('ONE','THREE','FOUR'));
            
        // Test set that clears it
        $nf->set_authids(null,array());
        $nf->save_changes();
        $nf2=$this->sys->get_feed($id);        
        $this->assertEqual($nf2->get_default_authid(),null);
        $this->assertEqualSort($nf2->get_optional_authids(),
            array());
    }
    
    private function assertEqualSort($array1,$array2) {
        sort($array1);
        sort($array2);
        $this->assertEqual($array1,$array2);
    }
    
    private function make_test_feed($i) {
        $nf=new internal_news_feed();
        $nf->set_blockinstance($i);
        $nf->set_name('Test feed '.$i);
        $nf->create_new();
        if($nf->get_id()!=$i) {
            throw new Exception("Unexpected ID for test feed $i");
        }
        return $nf;
    }
    
    function test_includes() {
        // Set up test feeds
        $nf1=$this->make_test_feed(1);
        $nf2=$this->make_test_feed(2);
        $nf3=$this->make_test_feed(3);
        
        // With no included feeds
        $this->assertEqual($nf1->get_included_feeds(),array());
        
        // OK, add a feed or two
        $nf1->add_included_feed(2);
        $nf1->add_included_feed($nf3);
        
        // Get again (should reload)
        $feeds=$nf1->get_included_feeds();
        $this->assertEqual(count($feeds),2);
        $this->assertEqualSort(array($feeds[0]->get_id(),$feeds[1]->get_id()),
            array(2,3));
        
        // Remove feeds
        $nf1->remove_included_feed(2);
        $nf1->remove_included_feed($nf3);
        $this->assertEqual($nf1->get_included_feeds(),array());
        
        // OK now try adding feeds pretending it's another instance...
        $newnf=feed_system::$inst->get_feed(1);
        $newnf->add_included_feed($nf2);
        $newnf->add_included_feed($nf3);
        $this->assertEqual($nf1->get_included_feeds(),array()); // Still using cache
        $this->assertEqual(count($nf1->get_included_feeds(false)),2); // Without cache
        
        // OK, fine, so we have feed 1 owns 2 and 3. Let's make 2 own 4, 4 own 5...
        $nf4=$this->make_test_feed(4);
        $nf2->add_included_feed($nf4);
        $nf5=$this->make_test_feed(5);
        $nf4->add_included_feed($nf5);
        
        // 6 isn't owned by anyone
        $nf6=$this->make_test_feed(6);
        $nf6->set_blockinstance(6);
        
        // Now test parent/child stuff... on an unconnected feed...
        $this->assertEqualSort($nf6->get_all_descendant_ids(),array(6));
        $this->assertEqualSort($nf6->get_all_ancestor_ids(),array(6));
        // ...on a feed with single descendant/parent...
        $this->assertEqualSort($nf4->get_all_descendant_ids(),array(4,5));
        $this->assertEqualSort($nf2->get_all_ancestor_ids(),array(1,2));
        // ...and with lots of 'em
        $this->assertEqualSort($nf1->get_all_descendant_ids(),array(1,2,3,4,5));
        $this->assertEqualSort($nf5->get_all_ancestor_ids(),array(1,2,4,5));
        // Test the bidirectional version
        $this->assertEqualSort($nf4->get_all_ancestor_ids(),array(1,2,4));
        $this->assertEqualSort($nf4->get_all_relatives_ids(),array(1,2,4,5));
        
        // Ok now can we just check the including feeds list
        $this->assertEqual($this->sys->get_including_feeds(6),array());
        $this->assertEqual(count($including=$this->sys->get_including_feeds(4)),1);
        $this->assertEqual($including[0]->get_id(),2);
        
    }
    
    function test_internalfeed_saveasnew() {
        global $CFG;
        // Set up test feeds
        $nf1=$this->make_test_feed(1);
        $nf2=$this->make_test_feed(2);
        $nf3=$this->make_test_feed(3);
        
        // Have some includes...
        $nf1->add_included_feed($nf2);
        $nf1->add_included_feed($nf3);
        // and authids...
        $nf1->add_optional_authid('whatever');
        $nf1->add_optional_authid('else');
        // and roles...
        newsfeed_add_test_role($nf1,1);
        newsfeed_add_test_role($nf1,2);
        newsfeed_add_test_role($nf1,3,true);
        // and entries...
        $e1=$this->make_test_entry('1');
        $e1->set_date(0);
        $e1->save_new(true,1);
        $e1->set_html('New text');
        $e1->set_roll_forward(true);
        $e1->set_date(strtotime('7 July 2006 10:00'));
        $e1->save_new();
        $e1->approve(2);
        $e2=$this->make_test_entry('2');
        $e2->set_roll_forward(false);
        // with attachments...
        file_put_contents($path=($CFG->dataroot.'/myfile.doc'),'hello there');
        $a1=news_attachment::create($path,basename($path));
        $e2->add_attachment($a1);
        file_put_contents($path=($CFG->dataroot.'/myfile2.doc'),'hello there as well');
        $a2=news_attachment::create($path,basename($path));
        $e2->add_attachment($a2);                
        $e2->save_new(true,1);
        $e2->approve(1);
        // and a non-approved entry which won't copy at all
        $e3=$this->make_test_entry('3');
        $e3->save_new(true,1); // But don't approve it
        // and an approved but deleted entry which also won't copy at all
        $e4=$this->make_test_entry('4');
        $e4->save_new(true,1); 
        $e4->set_deleted(true);
        $e4->save_new();
        $e4->approve(1);
        
        // Whew! I think that's everything. Okay, cool, now duplicate the feed
        $nf1id = $nf1->get_id(); // nf seems to get overwritten by save_as_new()
        $this->assertEqual(4,$nf1->save_as_new(0,4,0));
        $nf1=$this->sys->get_feed($nf1id); // nf seems to get overwritten by save_as_new()
        // Reload it
        $f=$this->sys->get_feed(4);
        newsfeed_add_test_role($f,1);
        newsfeed_add_test_role($f,2);
        newsfeed_add_test_role($f,3,true);
        $this->assertEqual($f->get_name(),'Test feed 1'); // Basics
        $this->assertEqualSort($f->get_all_descendant_ids(),array(2,3,4)); // Includes        
        $this->assertEqualSort($f->get_optional_authids(),array('whatever','else')); // Authids
        $roles=$f->get_feed_users();
        $this->assertEqual(count($roles),2);
        $posters=$roles['poster'];
        $this->assertEqual(count($posters),2);
        $this->assertEqual(array($posters[0]->get_user_id(),$posters[1]->get_user_id()),array(1,2));
        $approvers=$roles['approver'];
        $this->assertEqual(count($approvers),1);
        $this->assertEqual($approvers[0]->get_user_id(),3);
        $entries=$f->get_own_entries(); // Get all
        $this->assertEqual(count($entries),2); // Should only have the approved ones
        $this->assertEqual($entries[1]->get_html(),'New text');
        $this->assertEqual($entries[1]->get_date(),strtotime('7 July 2006 10:00'));
        $this->assertEqual($entries[1]->get_approver_userid(),2); // Approver set to 2
        $this->assertEqual(count($f->get_entry_history($entries[1]->get_entry_id())),1); // Doesn't have older version        
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/4/3_myfile.doc'));
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/4/3_myfile2.doc'));
        $attachments=$entries[0]->get_attachments();
        $this->assertEqual(count($attachments),2);        
        $this->assertWantedPattern('/^3_myfile2?\.doc$/',$attachments[0]->get_filename());
                
        // Duplicate with rollforward: #1 same timezone
        // Note: newsfeed entries will roll forward accumulatively
        $timediff = (strtotime('1 August 2006') - $nf1->get_start_date());
        $this->assertEqual(5,$nf1->save_as_new(strtotime('1 August 2006'), 5, $timediff));
        $nf1=$this->sys->get_feed($nf1id); // nf seems to get overwritten by save_as_new()
        $f=$this->sys->get_feed(5);
        $entries=$f->get_own_entries(); // Get all
        $this->assertEqual(count($entries),1); // Lose the non-rollforward one
        $this->assertEqual($entries[0]->get_date(),strtotime('6 September 2006 10:00')); // 07 Jul 2008 + 61 days
        $this->assertEqual($f->get_start_date(),strtotime('1 June 2006'));
        
        // #2 different timezone
        $timediff = (strtotime('1 November 2006') - $nf1->get_start_date());
        $this->assertEqual(6,$nf1->save_as_new(strtotime('1 November 2006'), 6, $timediff));
        $nf1=$this->sys->get_feed($nf1id); // nf seems to get overwritten by save_as_new()
        $f=$this->sys->get_feed(6);
        $entries=$f->get_own_entries(); // Get all
        $this->assertEqual($entries[0]->get_date(),strtotime('7 December 2006 10:00')); // 07 Jul 2008 + 153 days 
        
        // #3 overlap timezones
        $timediff = (strtotime('27 October 2006') - $nf1->get_start_date());
        $this->assertEqual(7,$nf1->save_as_new(strtotime('27 October 2006'), 7, $timediff));
        $nf1=$this->sys->get_feed($nf1id); // nf seems to get overwritten by save_as_new()
        $f=$this->sys->get_feed(7);
        $entries=$f->get_own_entries(); // Get all
        $this->assertEqual($entries[0]->get_date(),strtotime('2 December 2006 10:00')); // 07 Jul 2008 + 148 days
        
        // #4 same bit with a non-zero time offset
        $timediff = (strtotime('27 October 2006 08:00') - $nf1->get_start_date());
        $this->assertEqual(8,$nf1->save_as_new(strtotime('27 October 2006 08:00'), 8, $timediff));
        $f=$this->sys->get_feed(8);
        $entries=$f->get_own_entries(); // Get all
        $this->assertEqual($entries[0]->get_date(),strtotime('2 December 2006 18:00')); // 07 Jul 2008 + 148 days 8 hours
    }
    
    function test_internalfeed_rollforward() {
        
        $feb06=strtotime('1 February 2006');
        $nov06=strtotime('1 November 2006');
        $nov806=strtotime('8 November 2006');
        $feb07=strtotime('1 February 2007');
        $nov07=strtotime('1 November 2007');
        $nov807=strtotime('8 November 2007');
        
        // Set up test feeds - can no longer set start date and presentation
        $nf1=$this->make_test_feed(1);
        //$nf1->set_start_date($nov06);
        $nf1->save_changes();
        
        $nf2=$this->make_test_feed(2); // No pres code
        $nf3=$this->make_test_feed(3); 
        //$nf3->set_pres('06B'); // Different pres code
        //$nf3->set_start_date($feb06);
        $nf3->save_changes();
        $nf4=$this->make_test_feed(4);
        //$nf4->set_pres('06K'); // Same pres code
        //$nf4->set_start_date($nov806);
        $nf4->save_changes();
        $nf5=$this->make_test_feed(5);
        //$nf5->set_pres('06K'); // Also same pres code
        //$nf5->set_start_date($nov06);
        $nf5->save_changes();
        $nf1->add_included_feed($nf2);
        $nf1->add_included_feed($nf3);
        $nf1->add_included_feed($nf4);
        $nf1->add_included_feed($nf5);
        
        // Try rollforward without pres code first...
        $nfnew=feed_system::$inst->get_feed($nf1->get_id());
        $nfnew->roll_forward('', $nov07, 6, ($nov07 - $nov06));
        $nftest=feed_system::$inst->get_feed($nfnew->get_id());
        $this->assertEqual($nftest->get_start_date(),strtotime('1 June 2006'));
        $this->assertEqual($nftest->get_pres(),'06K');
        $included=$nftest->get_included_feeds();
        // Check it still has the original feeds
        $this->assertEqualSort($nftest->get_all_descendant_ids(),array(
            $nftest->get_id(),$nf2->get_id(),$nf3->get_id(),$nf4->get_id(),$nf5->get_id()));
        
        // Now try it with a pres code but with original feed
        // not having one
        $nfnew=feed_system::$inst->get_feed($nf1->get_id());
        $nfnew->roll_forward('07K',$nov07, 7, ($nov07 - $nov06));
        $nftest=feed_system::$inst->get_feed($nfnew->get_id());
        $this->assertEqual($nftest->get_start_date(),strtotime('1 June 2006'));
        $this->assertEqual($nftest->get_pres(),'06K');
        $included=$nftest->get_included_feeds();
        // Check it still has the original feeds
        $this->assertEqualSort($nftest->get_all_descendant_ids(),array(
            $nftest->get_id(),$nf2->get_id(),$nf3->get_id(),$nf4->get_id(),$nf5->get_id()));
        
        // Roll forward nf5 so that it's in the 'already done' category
        $nf5new=feed_system::$inst->get_feed($nf5->get_id());
        $nf5new->roll_forward('07K',$nov07, 8, ($nov07 - $nov06));
        
        // Set original feed to pres code
        //$nf1->set_pres('06K');
        $nf1->save_changes();

        // Roll forward for real        
        $nfnew=feed_system::$inst->get_feed($nf1->get_id());
        $nfnew->roll_forward('07K',$nov07, 9, ($nov07 - $nov06));
        $nftest=feed_system::$inst->get_feed($nfnew->get_id());
        $this->assertEqual($nftest->get_start_date(),strtotime('1 June 2006'));
        //$this->assertEqual($nftest->get_pres(),'07K');
        
        // Test nf4 has rolled forward
/* included feeds are no longer rolled forward
 * they are only added as included feeds if they already exist
 */ 
        $newnf4=$this->sys->get_feed($nf4->get_id());
        $this->assertTrue($newnf4!=null);
        $this->assertEqual($newnf4->get_pres(),'06K');
        $this->assertEqual($newnf4->get_start_date(),strtotime('1 June 2006'));
        
        // Check that we have: original 2, not original 3 (diff. pres), new 4, new 5 (from before)
        $this->assertEqualSort($nftest->get_all_descendant_ids(),array(
/* included feeds are no longer rolled forward
 * they are only added as included feeds if they already exist 
            $nftest->get_id(),$nf2->get_id(),$newnf4->get_id(),$nf5new->get_id()));
 */
            $nftest->get_id(),$nf2->get_id(),$nf3->get_id(),$nf4->get_id(),$nf5->get_id()));
    }
    
    function test_get_due_external_feeds() {
        $now=time();
        
        $nf=new external_news_feed();
        $nf->set_name('Test feed');
        $nf->set_url('http://www.whatever.com/');
        $nf->set_check_freq(60);
        $nf->set_last_check($now-120); // Needs checking
        $id1=$nf->create_new(); 

        $nf=new external_news_feed();
        $nf->set_name('Test feed');
        $nf->set_url('http://www.whatever.com/');
        $nf->set_check_freq(60);
        $nf->set_last_check($now-30); // Does not need checking
        
        $result=$this->sys->get_due_external_feeds();
        $this->assertEqual(count($result),1);
        $this->assertEqual($result[0]->get_id(),$id1);
    }
    
    function make_test_entry($title) {
        $v=new news_entry_version;
        $v->set_title($title);
        $v->set_html('Text');        
        return $v;
    }
}
?>
