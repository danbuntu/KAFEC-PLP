<?php

require_once('newsfeed_test_base.php');
class test_build extends newsfeed_test_base {
    
    function make_example_feed() {
        // Make feed to use
        $nf=$this->make_test_feed(1);
        $nf->save_changes();
        
        // Make some entries
        $e1=$this->make_test_entry('First');
        $e1->set_date(strtotime('2 June 2006'));
        $e1->save_new(true,1);
        $e1->approve(1);
        
        $e2=$this->make_test_entry('Second');
        $e2->set_date(strtotime('3 June 2006'));
        $e2->set_link('http://www.example.com/');
        $e2->set_roll_forward(true);
        $e2->save_new(true,1);
        $e2->approve(1);
        
        $e3=$this->make_test_entry('Third');
        $e3->set_date(strtotime('4 June 2006'));
        $e3->save_new(true,1);
        $e3->approve(1);

        $e4=$this->make_test_entry('Fourth');
        $e4->set_date(strtotime('4 June 2020'));
        $e4->save_new(true,1);
        $e4->approve(1);
        return $nf;
    }
    
    function test_basic() {
        $nf=$this->make_example_feed();
        
        // OK now build that sucker
        global $CFG;
        $file=$CFG->dataroot.'/test.xml';
        $nf->build($file);
        
        //print '<pre>'.htmlspecialchars(file_get_contents($file)).'</pre>';
                 
        // Parse it
        $doc=DOMDocument::loadXML(file_get_contents($file));
        $xp=new DOMXPath($doc);
        $xp->registerNamespace('atom',news_feed::NS_ATOM);
        $xp->registerNamespace('ou',news_feed::NS_OU);
        
        // Got all the entries, do some fairly random tests on them
        $this->assertEqual(self::xpath($xp,'count(/atom:feed/atom:entry)'),3);
        $this->assertEqual(self::xpath($xp,'/atom:feed/@ou:public'),'yes');
        $this->assertEqual(self::xpath($xp,'/atom:feed/@ou:expires'),strtotime('4 June 2020'));
        $this->assertWantedPattern(
            '/^tag:.*,2006:newsfeedentry\/3$/',
            self::xpath($xp,'/atom:feed/atom:entry[position()=1]/atom:id'));
        $this->assertEqual(self::xpath($xp,'/atom:feed/atom:entry[position()=2]/atom:title'),'Second');
        $this->assertEqual(self::xpath($xp,'/atom:feed/atom:entry[position()=2]/atom:link/@href'),'http://www.example.com/');
        $this->assertEqual(self::xpath($xp,'/atom:feed/atom:entry[position()=3]/atom:content'),'Text');
        $this->assertEqual(self::xpath($xp,'/atom:feed/atom:entry[position()=1]/atom:published'),
            '2006-06-03T23:00:00Z');
        $this->assertEqual(self::xpath($xp,'/atom:feed/atom:entry[position()=2]/atom:published'),
            '2006-06-02T23:00:00Z');
        $this->assertTrue(
          abs(time()-strtotime(self::xpath($xp,'/atom:feed/atom:entry[position()=3]/atom:updated'))) < 60);
        $this->assertTrue(
          abs(time()-strtotime(self::xpath($xp,'/atom:feed/atom:updated'))) < 60);     
          
        // And check it after we get the 2020 entry
        $nf->build($file,strtotime('5 July 2020'));
        $doc=DOMDocument::loadXML(file_get_contents($file));
        $xp=new DOMXPath($doc);
        $xp->registerNamespace('atom',news_feed::NS_ATOM);
        $xp->registerNamespace('ou',news_feed::NS_OU);
        $this->assertEqual(self::xpath($xp,'/atom:feed/@ou:expires'),'');
        $this->assertEqual(self::xpath($xp,'count(/atom:feed/atom:entry)'),4);
        $this->assertEqual(
          self::xpath($xp,'/atom:feed/atom:entry[position()=0]/atom:updated'),
          self::xpath($xp,'/atom:feed/atom:entry[position()=0]/atom:published'));
    }   
    
    function test_cache() {
        global $CFG;
        $nf=$this->make_example_feed();
        
        // Get data (as internal and external) and check it's cached
        $this->sys->get_feed_data(1,true);
        $this->sys->get_feed_data(1,false);
        $this->assert_feed_cache(array(1=>true));
        
        // Make it not public...
        $nf->set_public(false);
        $nf->save_changes();
        // ...and check it's cleared the cache
        $this->assert_feed_cache(array(1=>false));
        // Can still get it internally
        $this->sys->get_feed_data(1,false);
        $this->assert_feed_cache(array(1=>true));
        // But not externally
        try {
            $this->sys->get_feed_data(1,true);
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertEqual($e->getCode(),EXN_NEWSFEED_FEEDNOTPUBLIC);
        }
        
        // Fine, now test all the caching stuff
        // 1 includes 2; 2 includes 3 and 4; 5 includes 4
        $nf2=$this->make_test_feed(2);
        $nf3=$this->make_test_feed(3);
        $nf4=$this->make_test_feed(4);
        $nf5=$this->make_test_feed(5);
        $nf2->add_included_feed($nf3);
        $nf2->add_included_feed($nf4);
        $nf2->save_changes();
        $nf5->add_included_feed($nf4);
        $nf5->save_changes();
        $nf->add_included_feed($nf2);
        $nf->save_changes();
        
        for($i=1;$i<=5;$i++) {
            $this->sys->get_feed_data($i,false);
        }
        $this->assert_feed_cache(array(1=>true,2=>true,3=>true,4=>true,5=>true));        
        // Save something to feed 3 (should delete 3, 1, and 2... only after approve) 
        $nev=$this->make_test_entry('Ho');
        $nev->save_new(true,3);
        $this->assert_feed_cache(array(1=>true,2=>true,3=>true,4=>true,5=>true));
        $nev->approve(1);
        $this->assert_feed_cache(array(1=>false,2=>false,3=>false,4=>true,5=>true));
        
        for($i=1;$i<=5;$i++) {
            $this->sys->get_feed_data($i,false);
        }
        $this->assert_feed_cache(array(1=>true,2=>true,3=>true,4=>true,5=>true));
        // Save feed 4 (should delete all except 3)
        $nf4->set_summary('Yes well');
        $nf4->save_changes();
        $this->assert_feed_cache(array(1=>false,2=>false,3=>true,4=>false,5=>false));
    } 
    
    /**
     * @param array $expected Associative array of feed id->true/false 
     */
    private function assert_feed_cache($expected) {
        global $CFG;
        $actual=array();
        foreach($expected as $id=>$presence) {
            $actual[$id]=file_exists($CFG->dataroot."/newsfeed/cache/$id.atom");
        }
        $this->assertEqual($expected,$actual);
    }
    
    private static function xpath($xpath,$query) {
        $result=$xpath->evaluate($query);
        if(is_a($result,'DOMNodeList')) {
            $new='';
            for($i=0;$i<$result->length;$i++) {
                $new.=$result->item($i)->nodeValue;
            }
            $result=$new;
        }
        return $result;
    }
    
    private function make_test_feed($i,$name='Feed',$folder=1) {
        $nf=new internal_news_feed();
        $nf->set_name($name);
        $nf->set_blockinstance($i);
        $nf->create_new();
        if($nf->get_id()!=$i) {
            throw new Exception("Unexpected ID for test feed $i: ".$nf->get_id());
        }
        return $nf;
    }
    
    private function make_test_entry($title,$text='Text') {
        $v=new news_entry_version;
        $v->set_title($title);
        $v->set_html($text);        
        return $v;
    }    
}
?>
