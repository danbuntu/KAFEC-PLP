<?php

require_once('newsfeed_test_base.php');

class test_entries extends newsfeed_test_base {
    
    function test_fields() {
        // Create feeds for test
        $nf=new internal_news_feed();
        $nf->set_name('Test feed');
        $nf->set_blockinstance(1);
        $nf->create_new();
        
        // Date fields
        $v=new news_entry_version;        
        $this->assertTrue(abs(time() - $v->get_date()) < 2); // Default
        try {
            $v->set_date('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_DATEINVALID);
        }
        $v->set_date($correct=strtotime("1 June 2006"));
        $this->assertEqual($correct,$v->get_date());
                
        try {
            $v->set_roll_forward('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_ROLLFORWARDINVALID);
        }
        $this->assertEqual(false,$v->should_roll_forward()); // Default        
        $v->set_roll_forward(true);
        $this->assertEqual(true,$v->should_roll_forward());        
        
        // Other fields
        try {
            $v->set_authid('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_AUTHIDINVALID);
        }
        $v->set_authid('FROG');
        $this->assertEqual('FROG',$v->get_authid());
        try {
            $v->set_title(null);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_TITLEINVALID);
        }
        $v->set_title('Title');
        $this->assertEqual('Title',$v->get_title());
        try {
            $v->set_html(null);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_HTMLINVALID);
        }
        $v->set_html('Text');
        $this->assertEqual('Text',$v->get_html());
                
        try {
            $v->set_poster('fred');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_USERINVALID);
        }
        $v->set_poster(1);
        $this->assertEqual(1,$v->get_poster_userid());
        $this->assertTrue(abs(time() - $v->get_time_posted()) < 2);
        
        try {
            $v->set_link('');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_LINKINVALID);
        }
        $v->set_link(null);
        $this->assertEqual(null,$v->get_link());
        $v->set_link('http://whatever');
        $this->assertEqual('http://whatever',$v->get_link());

        try {
            $v->approve(2);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_CHANGEDAPPROVE);
        }
        
        // Check we can save it
        $this->assertEqual(1,$v->save_new(true,$nf->get_id()));
        
        // Check approve works now
        $v->approve(2);
        
        // Now save as new and check it's not approved
        $v->set_deleted(true);
        $v->save_new();
        $this->assertEqual(null,$v->get_approver_userid());
        
        // Now try a new one without setting required fields
        $v=new news_entry_version(); 
        try {
            $v->save_new(true,$nf->get_id());
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_TITLEINVALID);
        }
        
        // OK now test retrieving the data
        $entries=$nf->get_own_entries(false,true);
        $this->assertEqual(count($entries),1);
        $v=$entries[0];
        $this->assertEqual($v->get_id(),2);
        $this->assertEqual($v->get_date(),$correct);
        $this->assertEqual($v->should_roll_forward(),true);
        $this->assertEqual($v->get_authid(),'FROG');
        $this->assertEqual($v->get_title(),'Title');
        $this->assertEqual($v->get_html(),'Text');
        $this->assertEqual($v->get_link(),'http://whatever');
        $this->assertEqual($v->get_poster_userid(),1);
        $this->assertEqual($v->is_deleted(),true);
        $this->assertTrue(abs(time() - $v->get_time_posted()) < 60);
        $this->assertEqual($v->get_time_approved(),null);
    }
    
    private function make_test_feed($i) {
        $nf=new internal_news_feed();
        $nf->set_name('Test feed '.$i);
        $nf->set_blockinstance($i);
        $nf->create_new();
        if($nf->get_id()!=$i) {
            throw new Exception("Unexpected ID for test feed $i");
        }
        return $nf;
    }
    
    function test_save_load() {
        // Create test feeds
        $nf=$this->make_test_feed(1);
        $nf2=$this->make_test_feed(2);
        
        // Create basic entry
        $v=new news_entry_version;
        $v->set_title('Title');
        $v->set_html('Text');        
        
        // OK now try saving...
        try {
            $v->save_new();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NOENTRY);
        }
        try {
            $v->save_new(true);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NONEWSFEED);
        }
        $this->assertEqual(1,$v->save_new(true,$nf->get_id()));
        try {
            $v->save_new(false,1);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NOTNEWENTRY);
        }        
        // Change the text and try again [new version of same one]
        $v->set_html('Text 2');
        $this->assertEqual(2,$v->save_new());
        // Same data but new entry and older date (so it appears second)
        $v->set_date(time()-60);
        $this->assertEqual(3,$v->save_new(true));
        // Same data in different feed 
        $this->assertEqual(4,$v->save_new(true,$nf2->get_id()));
        
        // Right fine, now get it back from first feed (should have
        // entries 2 and 3)...
        $entries=$nf->get_own_entries();
        $this->assertEqual($this->get_entry_ids($entries),array(2,3));
        $this->assertTrue(!$entries[0]->is_changed());
        
        // OK cool now how about reordering
        $entries[1]->set_date(time()+60);
        $this->assertEqual(5,$entries[1]->save_new());        
        $entries=$nf->get_own_entries();
        $this->assertEqual($this->get_entry_ids($entries),array(5,2));
        
        // Now for relative dates vs absolute
        // note: We no longer do this in the database but I left in
        // the code just because, & so that I didn't have to change
        // later parts of test
        $entries[0]->set_date($nf->get_start_date()+100);
        $this->assertEqual(6,$entries[0]->save_new());        
        $entries[1]->set_date($nf->get_start_date()+101);
        $this->assertEqual(7,$entries[1]->save_new());        
        $entries=$nf->get_own_entries();
        $this->assertEqual($this->get_entry_ids($entries),array(7,6));
        $entries[0]->set_date($nf->get_start_date()+99);
        $this->assertEqual(8,$entries[0]->save_new());        
        $entries=$nf->get_own_entries();
        $this->assertEqual($this->get_entry_ids($entries),array(6,8));
        
        // Fine, now so far we didn't approve anything
        $aentries=$nf->get_own_entries(true);
        $this->assertEqual($this->get_entry_ids($aentries),array());
        
        // Approve an entry and check it now appears
        $entries[0]->approve(1);
        $aentries=$nf->get_own_entries(true);
        $this->assertEqual($this->get_entry_ids($aentries),array(6));
        
        // Post a new version of #6 but not approved; approve 8
        $entries[0]->save_new();
        $entries[1]->approve(13);
        $aentries=$nf->get_own_entries(true);
        $this->assertEqual($this->get_entry_ids($aentries),array(6,8));
        
        // And the unapproved one should appear in the ordinary list
        $entries=$nf->get_own_entries();
        $this->assertEqual($this->get_entry_ids($entries),array(9,8));
    }
    
    function make_test_entry($title) {
        $v=new news_entry_version;
        $v->set_title($title);
        $v->set_html('Text');        
        return $v;
    }
    
    function test_get_entries() {
        // Create feeds and adjust startdates
        $nf1=$this->make_test_feed(1);
        $nf1->save_changes();
        $nf2=$this->make_test_feed(2);
        $nf2->save_changes();
        $nf3=$this->make_test_feed(3);
        $nf3->save_changes();
        
        // Add linking. 1 includes 2 includes 3
        $nf1->add_included_feed($nf2);
        $nf2->add_included_feed($nf3);
        
        // Add entries. Feed 1 has 'middle-date' entries, 2 has newer, 3 older.
        $v=$this->make_test_entry('#1');
        $v->set_date(11000);
        $v->save_new(true,3);
        $v->approve(1);
        $first=$v;
        $v=$this->make_test_entry('#2');
        $v->set_date(12000);
        $v->save_new(true,3);
        $notapproved=$v;
        $v=$this->make_test_entry('#3');
        $v->set_date(20500);
        $v->save_new(true,1);
        $v->approve(1);
        $v=$this->make_test_entry('#4');
        $v->set_date(21000);
        $v->save_new(true,1);
        $v->approve(1);
        $v=$this->make_test_entry('#5');
        $v->set_date(31000);
        $v->save_new(true,2);
        $v->approve(1);
        $v=$this->make_test_entry('#6');
        $v->set_date(32000);
        $v->save_new(true,2);
        $v->approve(1);
        
        // Check that we have all the entries (except the unapproved one)
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries()),array(6,5,4,3,1));
            
        // OK approve the missing one and check again
        $notapproved->approve(1);
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries()),array(6,5,4,3,2,1));
        
        // Now do a new version of one of the oldest ones
        $first->save_new();
        $first->approve(1);
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries()),array(6,5,4,3,2,7));
            
        // And change date to put it first
        $first->set_date(33000);
        $first->set_poster(3); // Just for testing poster/approver info later
        $first->save_new();
        $first->approve(1);
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries()),array(8,6,5,4,3,2));
            
        // Now test some effective dates
        $entries=$nf1->get_entries();
        $this->assertEqual($entries[0]->get_date(),33000); // Abs
        $this->assertEqual($entries[1]->get_date(),32000); // Rel (used to be)
        
        // Test get earliest date
        $this->assertEqual($nf1->get_earliest_date(),12000);
        
        // Test poster/approver:
        
        // a) non-retrieved from database
        try {
            $first->get_poster_realname();
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NOTFROMDB);
        }
        
        // b) retrieved but not present
        $this->assertEqual($entries[1]->get_poster_username(),null);        
        
        // c) retrieved OK
        $this->assertEqual($entries[0]->get_poster_userid(),3);        
        $this->assertEqual($entries[0]->get_poster_username(),'u3');        
        $this->assertEqual($entries[0]->get_poster_realname(),'user three');        
        $this->assertEqual($entries[0]->get_approver_userid(),1);        
        $this->assertEqual($entries[0]->get_approver_username(),'u1');        
        $this->assertEqual($entries[0]->get_approver_realname(),'user one'); 
        
        // Test delete
        $entries[0]->set_deleted(true);
        $entries[0]->save_new();
        // No change, delete not approved
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries()),array(8,6,5,4,3,2));            
        $entries[0]->approve(1);
        // Delete approved, entry gone
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries()),array(6,5,4,3,2));
        // Unless you call get_entries with the relevant flag
        $this->assertEqual(
            $this->get_entry_ids($nf1->get_entries(true,null,true)),array(9,6,5,4,3,2));            
        // Can still get single entry
        $this->assertEqual(
           $nf3->get_entry_by_entry($entries[0]->get_entry_id())->get_id(),9);
        $this->assertEqual(
           $nf3->get_entry_by_version(9)->get_id(),9);                       
    }
    
    function test_history() {
        // Set up test situation: 2 feeds, one empty, other has 2 entries
        $nf1=$this->make_test_feed(1);
        $nf2=$this->make_test_feed(2);
        
        $v=$this->make_test_entry('#1');
        $v->save_new(true,1); // v1
        $id1=$v->get_id();
        $v->save_new(); // v2
        $id2=$v->get_id();
        $v->approve(1); // "
        $v->save_new(); // v3
        $id3=$v->get_id();
        
        $v2=$this->make_test_entry('#2');
        $v2->save_new(true,1); // v1
        
        // Made-up entry id
        try {
            $nf1->get_entry_history(666);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_INVALIDID);
        }
        // Exists but other feed
        try {
            $nf2->get_entry_history($v->get_entry_id());
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_INVALIDID);
        }
        
        // Get simple history (1 item)
        $simple=$nf1->get_entry_history($v2->get_entry_id());
        $this->assertEqual(count($simple),1);
        $this->assertEqual($simple[0]->get_id(),$v2->get_id());
        
        // Get complex history (3 items)
        $complex=$nf1->get_entry_history($v->get_entry_id());
        $this->assertEqual(count($complex),3);
        $this->assertEqual($complex[0]->get_id(),$id3);
        $this->assertEqual($complex[1]->get_id(),$id2);
        $this->assertEqual($complex[2]->get_id(),$id1);
    }
    
    function test_files() {
        global $CFG;
        
        $nf1=$this->make_test_feed(1);
        $v=$this->make_test_entry('#1');
        
        // No files initially
        $this->assertEqual(count($v->get_attachments()),0);
        
        // Create test file
        file_put_contents($path=($CFG->dataroot.'/my_file.doc'),'hello there');
                
        // Check creating file that doesn't exist...
        try {
            $a=news_attachment::create($path.'sadgsdg','my_file.doc');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_NOSUCHFILE);
        }
        // ...one that does exist, with specified MIME type...
        $a=news_attachment::create($path,basename($path),'application/blah');
        $this->assertEqual($a->get_mime_type(),'application/blah');
        // ...and finally use default MIME type
        $a=news_attachment::create($path,basename($path));
        $this->assertEqual($a->get_mime_type(),'application/msword');
        $v->add_attachment($a);        

        file_put_contents($pngpath=($CFG->dataroot.'/asfasfasf'),'hello there');
        $mimeagain=news_attachment::create($pngpath,'myfile.png');
        $this->assertEqual($mimeagain->get_mime_type(),'image/png');
        
        // Add another one (so that we are testing multiples and also funny filenames) and save
        file_put_contents($path2=($CFG->dataroot.'/my file.html'),'hello hello again');        
        $a2=news_attachment::create($path2,basename($path2));
        $v->add_attachment($a2);        
        $this->assertEqual(count($v->get_attachments()),2);        
        $v->save_new(true,1);
        
        // Check files have been moved into right place
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/1/1_my_file.doc'));
        $this->assertTrue(!file_exists($path));
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/1/1_my_file.html'));
        $this->assertTrue(!file_exists($path2));
        
        // Now load entries and check file is still there
        $entries=$nf1->get_own_entries();
        $this->assertEqual(count($entries),1);
        $atts=$entries[0]->get_attachments();
        $this->assertEqual(count($atts),2);
        $this->assertEqual($atts[0]->get_filename(),'1_my_file.doc');
        $this->assertEqual($atts[0]->get_mime_type(),'application/msword');
        $this->assertEqual($atts[0]->get_size(),11);
        $this->assertEqual($atts[1]->get_filename(),'1_my_file.html');
        
        // What about when you make a new version, adding a new attachment?
        file_put_contents($path3=($CFG->dataroot.'/myfile2.html'),'hello once more');
        $a3=news_attachment::create($path3,basename($path3));
        $entries[0]->add_attachment($a3);        
        $this->assertEqual(count($entries[0]->get_attachments()),3);        
        $entries[0]->save_new();
        $entries=$nf1->get_own_entries();
        $this->assertEqual(count($entries),1);
        $atts=$entries[0]->get_attachments();
        $this->assertEqual(count($atts),3);
        
        // OK cool now let's remove one of them
        $entries[0]->remove_attachment($atts[0]);
        $entries[0]->save_new();               
        $entries=$nf1->get_own_entries();
        $this->assertEqual(count($entries),1);
        $atts=$entries[0]->get_attachments();
        $this->assertEqual(count($atts),2);
        $this->assertEqual($atts[0],$a2);
        $this->assertEqual($atts[1],$a3);
        
        // Now time to try saving to another feed
        $nf2=$this->make_test_feed(2);
        $entries[0]->save_new(true,2);

        // Files should now be present in both folders
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/1/1_my_file.html'));
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/2/1_my_file.html'));
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/1/2_myfile2.html'));
        $this->assertTrue(file_exists($CFG->dataroot.feed_system::FEED_FILES_FOLDER.'/2/2_myfile2.html'));
    }
    
    function test_wipe_all_entries() {
        // Test feed
        $nf=$this->make_test_feed(1);
        $nf2=$this->make_test_feed(2);

        // A couple test entries, one w/ two versions
        $e1=$this->make_test_entry('1');
        $e1->save_new(true,$nf->get_id());
        $e1->approve(2);
        $e2=$this->make_test_entry('2');
        $e2->save_new(true,$nf->get_id());
        
        $e3=$this->make_test_entry('3');
        $e3->save_new(true,$nf2->get_id());
        
        // A test file
        global $CFG;
        file_put_contents($path=($CFG->dataroot.'/my_file.doc'),'hello there');
        $a=news_attachment::create($path,basename($path));
        $e2->add_attachment($a);
        $e2->save_new();
        
        // Check how many entries there are now...
        $this->assertEqual(count($nf->get_own_entries(false,true)),2);
        $this->assertEqual(count($nf->get_own_entries(true,true)),1);
        $this->assertEqual(count($nf2->get_own_entries(false,true)),1);

        // Wipe them from first feed...
        $nf->wipe_all_entries();      
                
        // Now check again
        $this->assertEqual(count($nf->get_own_entries(false,true)),0);
        $this->assertEqual(count($nf->get_own_entries(true,true)),0);
        $this->assertEqual(count($nf2->get_own_entries(false,true)),1);
    }    
    
    private function get_entry_ids($entries) {
        $result=array();
        foreach($entries as $entry) {
            $result[]=$entry->get_id();
        }
        return $result;
    }
}
?>
