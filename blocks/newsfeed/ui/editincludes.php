<?php
/**
 * Form for editing includes of a newsfeed (the other feeds that it
 * incorporates).
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
require_once('../../../config.php');
require_once('../system/feed_system.php');
require_once('sharedui.php');

$courseid=optional_param('courseid',0,PARAM_INT);
$returntofeed=optional_param('returntofeed','n',PARAM_RAW);
$pcourseid = $courseid ? '&courseid='.$courseid : '';
$preturntofeed = $returntofeed === 'y' ? '&returntofeed='.$returntofeed : '';

// Apply changes
if($_SERVER['REQUEST_METHOD']=='POST') {
    do_task_access();
    
    try {   
        $tw=new transaction_wrapper();
        
        $newsfeedid=required_param('thisnewsfeedid',PARAM_INT);
        $nf=feed_system::$inst->get_feed($newsfeedid);
        
        if(array_key_exists('action_add',$_POST)) {
            $includeid=required_param('newsfeedid',PARAM_INT);
            $nf->add_included_feed($includeid);
        } else if(array_key_exists('action_remove',$_POST)) {
            $removeid=required_param('removeid',PARAM_RAW);
            $matches=array();
            if(!preg_match('/^f([0-9]+)$/',$removeid,$matches)) {
                error('Unexpected value for removeid');
            }            
            $nf->remove_included_feed($matches[1]);
        } else {
            error("Unknown action");
        }
        
        $tw->commit();
    } catch(Exception $e) {
        error_exception($e,'Failed to edit includes: '.$e->getMessage());
    }
    
    redirect('editincludes.php?newsfeedid='.$newsfeedid.($courseid?'&courseid='.$courseid:'').$preturntofeed);
    exit;
}


// Display page
$newsfeedid=required_param('newsfeedid',PARAM_INT);
$nf=feed_system::$inst->get_feed($newsfeedid);

$nfname=htmlspecialchars($nf->get_name());
$internal=is_a($nf,'internal_news_feed');

do_admin_access_and_header($nfname,'viewfeed.php?newsfeedid='.$newsfeedid,
    get_string('editfeedsettings',LANGF));


print_editinclude_tabs('includes',$newsfeedid,$courseid);

print_simple_box_start('center');

if($internal) {
    $strincludeslist=get_string('includeslist',LANGF);
    $sesskey=sesskey();
    print "
<form method='post' action='editincludes.php'>
<input type='hidden' name='thisnewsfeedid' value='$newsfeedid' />
<input type='hidden' name='newsfeedid'/>
<input type='hidden' name='courseid' value='$courseid'/>
<input type='hidden' name='sesskey' value='$sesskey'/>
<input type='hidden' name='returntofeed' value='$returntofeed'/>
<div id='includes'>
<h4>$strincludeslist</h4>
<select size='10' name='removeid' id='includeslist' onchange='updateSelected()'>
";

    $includes=$nf->get_included_feeds();
    if(count($includes)==0) {
        $strnoincludes=get_string('noincludes',LANGF);
        print "<option disabled='disabled'>$strnoincludes</option>";
    }
    foreach($includes as $include) {
        $id=$include->get_id();
        $name=$include->get_folder()->get_path();
        if(!preg_match('/\/$/',$name)) {
            $name.='/';
        }
        $name.=$include->get_name();
        $name=htmlspecialchars($name);
        print "<option value='f$id'>$name</option>";
    }

    $stravailablelist=get_string('availablelist',LANGF);
    print "
</select>
<input type='button' id='js_jump' value='Jump to' onclick='location.href=\"editincludes.php?newsfeedid=\"+document.getElementById(\"includeslist\").value.slice(1)+\"".$pcourseid.$preturntofeed."\"' />
</div>
<div id='transfer'>
<input type='submit' id='action_add' name='action_add' value='&lt; Add'>
<input type='submit' id='action_remove' name='action_remove' value='Remove &gt;'>
</div>
<div id='available'>
<h4>$stravailablelist</h4>
";

print_hierarchy_list();

$filtered=implode(',',$nf->get_all_relatives_ids());

print "
</div>
</form>
<script style='text/javascript'>
function updateSelected() {
    var selected=xhierarchy_get_selected(); 
    util_enable(selected && selected.type=='feed',['action_add']);

    var list=document.getElementById('includeslist');
    util_enable(list.value,['action_remove','js_jump']);
}
xhierarchy_register_select_listener(updateSelected);
xhierarchy_filter_feeds([$filtered]);
xhierarchy_set_dblclick_action(function(id) {
    document.getElementById('action_add').click();
});              
updateSelected();
</script>
";    
}

$strincludinglist=get_string('includinglist',LANGF);
print "
<div id='including'>
<h4>$strincludinglist</h4>
";

$including=feed_system::$inst->get_including_feeds($newsfeedid);
if(count($including)>0) {

    // Require correct course id for including feeds
    // Note: Has been mentioned can get via newsfeed now but 
    // do_admin_access_and_header() uses optional parameter and if no course
    // id parameter but pass newsfeed will override admin-only page test
    // Need to discuss further before more global changes
    $biids = ''; 
    foreach($including as $includer) {
        $biids .= ', '.$includer->get_blockinstance();
    }
    $bicourseids = get_records_select('block_instance', 'id IN ('.substr($biids, 2).") AND pagetype = 'course-view'", '', 'id, pageid');

    print "<ul>";
    foreach($including as $includer) {
        $biid = $includer->get_blockinstance();
        $pcourseid = !empty($bicourseids[$biid]) ? '&courseid='.$bicourseids[$biid]->pageid : '';
        print '<li><a href="editincludes.php?newsfeedid='.$includer->get_id().$pcourseid.$preturntofeed.'">'.
            $includer->get_folder()->get_path().'/'.$includer->get_name().'</a></li>';
    }
    print "</ul>";
} else {
    $strnoincluding=get_string('noincluding',LANGF);
    print "<p>$strnoincluding</p>";
}

print "</div>";

print_simple_box_end();

// Footer
print_footer();
?>