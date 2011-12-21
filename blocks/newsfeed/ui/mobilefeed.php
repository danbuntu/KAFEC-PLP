<?php
/**
 * Feed for use with mobile devices.
 *
 * @copyright &copy; 2009 The Open University
 * @author a.j.forth@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */

require_once('sharedui.php');

function display_mobile_feed($newsfeedid){
	global $USER;
	
	$nf = get_newsfeed_or_error($newsfeedid);
	$entries=$nf->get_entries(false,$USER->id,true);

	$internal=is_a($nf,'internal_news_feed'); 
	
    // Nonediting version uses Atom feed, not database
    $doc=DOMDocument::loadXML(feed_system::$inst->get_feed_data($newsfeedid,false));
    $nl=$doc->getElementsByTagName('entry');
    print '<div class="mobile_newsfeed_entry">';
    for($i=0;$i<$nl->length;$i++) {
        print_atom_entry($nl->item($i));
    }
    print '</div>';
    
    if(!$internal && ($error=$nf->get_error()) && $allowupdate) {
        $msg=get_string('externalerror',LANGF,display_entry_date($nf->get_last_check(),true));
        print "<div class='nf_externalerror'>$msg: <span>$error</span></div>";
    }
}

?>