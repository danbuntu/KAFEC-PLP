<?php

require_once('newsfeed_test_base.php');
class test_external_feed extends newsfeed_test_base {
    function test_list_feed_items() {
        $doc=DOMDocument::loadXML('
<feed xmlns:atom="http://www.example.com/">
    <entry><!-- Atom 1 (link only) -->  
        <title type="text">AT&amp;T bought by SBC!</title>
        <updated>2003-12-13T18:30:02Z</updated>
        <link href="http://www.example.com/"/>
    </entry>
    <subthingy>
        <atom:entry><!-- Atom 2 (not specify type; published date should override; content should override; NS) -->  
            <atom:title>AT&amp;T bought by SBC!</atom:title>
            <atom:updated>2003-12-13T18:30:02Z</atom:updated>
            <atom:published>2003-01-13T18:30:02Z</atom:published>
            <atom:summary>OHOHO</atom:summary>
            <atom:content>AT&amp;T bought by SBC! Desc</atom:content>
        </atom:entry>
    </subthingy>
    <entry><!-- Atom 3 (HTML) -->
        <title type="html">
            AT&amp;amp;T bought &lt;b&gt;by SBC&lt;/b&gt;!
        </title>
        <updated>2003-12-13T18:30:02Z</updated>
        <summary type="html">
            AT&amp;amp;T bought &lt;b&gt;by SBC&lt;/b&gt;! Desc
        </summary>
    </entry>
    <entry><!-- Atom 4 (XHTML) -->
        <title type="xhtml">
          <div xmlns="http://www.w3.org/1999/xhtml">
            AT&amp;T bought <b>by SBC</b>!
          </div>
        </title>
        <updated>2003-12-13T18:30:02Z</updated>
        <summary type="xhtml">
          <div xmlns="http://www.w3.org/1999/xhtml">
            AT&amp;T bought <b>by SBC</b>! Desc
          </div>
        </summary>
    </entry>

    <!-- RSS 1 -->
    <item>
        <title>Atom-Powered Robots Run Amok</title>
        <link>http://example.org/2003/12/13/atom03</link>
        <guid isPermaLink="false">urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a</guid>
        <pubDate>Sat, 13 Dec 2003 18:30:02 GMT</pubDate>
        <description>Some text.</description>
    </item>

    <entry>
        <bogus/>
        <!-- Nothing in here -->
    </entry>
    <entry>
        <title>A title</title>
        <!-- But no description/link -->
    </entry>

</feed>
');
        
        $results=array();
        external_news_feed::list_feed_items($doc->documentElement,$results);
        
        $this->assertEqual(count($results),5);
        $title='AT&T bought by SBC!'; $desc='AT&amp;T bought <b>by SBC</b>! Desc';
        for($i=0;$i<4;$i++) {
            $this->assertEqual($results[$i]->title,$title);
            if($i>1) {
                $this->assertEqual($results[$i]->description,$desc);
            }
        }
        $this->assertTrue(empty($results[0]->description));
        $this->assertEqual($results[0]->link,'http://www.example.com/');
        $this->assertEqual($results[1]->description,'AT&amp;T bought by SBC! Desc');

        $this->assertEqual($results[4]->description,'Some text.');
        $this->assertEqual($results[4]->title,'Atom-Powered Robots Run Amok');
        $this->assertEqual($results[4]->link,'http://example.org/2003/12/13/atom03');

        $date1=strtotime('13 Dec 2003 18:30:02 GMT');
        $this->assertTrue($date1); // Make sure it parses OK
        $this->assertEqual($results[0]->date,$date1);
        $this->assertEqual($results[1]->date,strtotime('13 Jan 2003 18:30:02 GMT'));
        $this->assertEqual($results[4]->date,$date1);

    }
    
}
?>
