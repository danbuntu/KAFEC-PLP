<?php

require_once('newsfeed_test_base.php');

// Define folder - negative course id
define('ROOTFOLDER', 0);

class test_folders extends newsfeed_test_base {
    
    function test_get_root() {
        // Get root #1
        $lf=$this->sys->get_location_folder(ROOTFOLDER,null);
        $this->assertEqual($lf->get_name(),'');
        $this->assertEqual($lf->get_path(),'/');
        $this->assertEqual($lf->get_id(),ROOTFOLDER);
        
        // Get root #2
        $lf2=$this->sys->get_location_folder(null,'/');
        $this->assertEqual($lf,$lf2);
    }
    
    /* Folders are now categories so this is redundant */
    function xxxtest_make_and_get() {
        // Get root
        $root=$this->sys->get_location_folder(ROOTFOLDER,null);
        
        // Make 2 new folders
        $apple=$root->new_folder('apple');
        $banana=$root->new_folder('banana');
        
        // And a child folder of apple
        $gala=$apple->new_folder('gala');
        
        // And another one
        $nz=$gala->new_folder('nz');
        
        // Right, check basic details of one of these
        $this->assertEqual($gala->get_name(),'gala');
        $this->assertEqual($gala->get_id(),4);
        
        // Check path
        $this->assertEqual($nz->get_path(),'/apple/gala/nz');

        // Check it still works after getting it from DB                
        $tnz=$this->sys->get_location_folder(4,null);
        $this->assertEqual($tnz->get_path(),'/apple/gala');
        $tnz=$this->sys->get_location_folder(null,'/apple/gala/nz');
        $this->assertEqual($tnz->get_path(),'/apple/gala/nz');
    }
    
    /* Folders are now categories so this is redundant */
    function xxxtest_get_contents() {
        // Get root
        $root=$this->sys->get_location_folder(FOLDER,null);
        
        // Make 2 new folders in reverse order
        $banana=$root->new_folder('banana');
        $apple=$root->new_folder('apple');
        
        // Another folder (decoy)
        $gala=$apple->new_folder('gala');

        // Make some new feeds
        $this->make_test_feed(1,'zebra',$root);
        $this->make_test_feed(2,'aardvark',$root);
        $f3=$this->make_test_feed(3,'aaaaa',$banana);
        $f3->set_pres('06K');
        $f3->save_changes();
        
        // gala contents [empty]
        $this->assertEqual($gala->get_contents(),array());
        
        // banana contents [just feed]
        $this->assertEqual(
            $this->string_contents($banana->get_contents()),
            array('FEED:3:aaaaa 06K'));
            
        // apple contents [just folder]
        $this->assertEqual(
            $this->string_contents($apple->get_contents()),
            array('FOLDER:4:gala'));
        
        // root contents [mixed, sorting]
        $this->assertEqual(
            $this->string_contents($root->get_contents()),
            array('FOLDER:3:apple','FOLDER:2:banana',
                'FEED:2:aardvark','FEED:1:zebra'));    
                
        // Get feed that exists...
        $this->assertEqual($root->get_feed('zebra')->get_name(),'zebra');
        // ...doesn't exist in that folder...
        $this->assertEqual($root->get_feed('aaaaa'),null);
        // ...doesn't exist at all...
        $this->assertEqual($root->get_feed('heebiejeebie'),null);
        // ...exists but with wrong pres
        $this->assertEqual($banana->get_feed('aaaaa','07K'),null);
        $this->assertEqual($banana->get_feed('aaaaa'),null);
        // ...exists and with right pres
        $this->assertEqual($banana->get_feed('aaaaa','06K')->get_name(),'aaaaa');
                
        // Delete a folder
        $gala->delete();    
        $this->assertEqual(
            $this->string_contents($apple->get_contents()),
            array());
    }
    
    private function string_contents($contents) {
        $result=array();
        foreach($contents as $item) {
            $result[]=$item->get_debug_string();
        }
        return $result;        
    }  
    
    private function make_test_feed($i,$name,$folder) {
        $nf=new internal_news_feed();
        $nf->set_blockinstance($i);
        //$nf->set_folder($folder);
        $nf->set_name($name);
        $nf->create_new();
        if($nf->get_id()!=$i) {
            throw new Exception("Unexpected ID for test feed $i");
        }
        return $nf;
    }
    
}
?>
