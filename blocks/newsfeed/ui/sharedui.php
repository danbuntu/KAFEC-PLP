<?php
/**
 * Shared UI functions for the newsfeed stuff.
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */
define('LANGF','block_newsfeed');

function get_newsfeed_or_error($newsfeedid) {
    try {
        return feed_system::$inst->get_feed($newsfeedid);
    } catch(Exception $e) {
        error("No such newsfeed: $newsfeedid");
    }
}

function require_newsfeed_access($nf) {
    $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
    if(!(has_capability('block/newsfeed:post', $nfcontext) ||
         has_capability('block/newsfeed:approve', $nfcontext))) {
        error("You don't have permission to access this newsfeed.");
    }
}


function do_admin_access_and_header($pagename='',$firstpagelink='',$secondpagename='',$extrabuttons='',$nf=null,$meta='',$feedicon='') {
    // Control access
    require_login();
    $systemcontext = get_context_instance(CONTEXT_SYSTEM);
    if(!has_capability('block/newsfeed:manage', $systemcontext)) {
        if(!$nf) {
            error('This page is admin-only.');
        } else {
        $nfcontext = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());
        if(!(has_capability('block/newsfeed:post', $nfcontext) ||
             has_capability('block/newsfeed:approve', $nfcontext))) {
                error('You do not have access to this page');
            }
        }
    }

    // Header
    if (!$site = get_site()) {
        error("Site isn't defined!");
    }
    $meta.='<script type="text/javascript" src="newsfeed.js"></script>';

    $strfeedlist=get_string('feedlist','block_newsfeed');

    global $CFG;
    $courseid=optional_param('courseid',0,PARAM_INT);
    $nav=array();
    if($courseid) {
        $coursename=get_field('course','shortname','id',$courseid);
				$nav[]=array('name'=>$coursename,'link'=>$CFG->wwwroot.'/course/view.php?id='.$courseid,'type'=>'course');
    }

    if($pagename) {
        if($secondpagename) {
    				$nav[]=array('name'=>$pagename,'link'=>$firstpagelink.($courseid?'&courseid='.$courseid:''),'type'=>'newsfeed');
    				$nav[]=array('name'=>$secondpagename,'type'=>'newsfeed');
        } else {
    				$nav[]=array('name'=>$pagename,'type'=>'newsfeed');
        }
    } else {
        $pagename=$strfeedlist;
    }

    print_header("$site->shortname: $pagename", "$site->fullname",
        build_navigation($nav),'',$meta,true,$feedicon.$extrabuttons) ;
}

function do_task_access($adminonly=true) {
    // Control access
    require_login();
    $systemcontext = get_context_instance(CONTEXT_SYSTEM);
    if($adminonly && !has_capability('block/newsfeed:manage', $systemcontext)) {
        error('This page is admin-only.');
    }
    check_post_and_sesskey();
}

function check_post_and_sesskey() {
    if($_SERVER['REQUEST_METHOD']!='POST') {
        error('This page only processes POST requests.');
    }
    // TODO Is this the right way to check sesskey?
    if(required_param('sesskey',PARAM_RAW)!=sesskey()) {
        error('Session key required');
    }
}

/**
 * Prints the settings/includes tabs.
 * @param string $current 'edit' or 'includes'
 * @param int $newsfeedid Newsfeed ID or zero if in creation process (can't switch to includes)
 * @param int $courseid Course ID or 0
 */
function print_editinclude_tabs($current,$newsfeedid,$courseid) {
    global $CFG, $USER;
		$coursebit=($courseid?'&courseid='.$courseid:'');

    // Preserve any return to feed parameter
    $returntofeed = '';
    if (optional_param('returntofeed','n',PARAM_RAW) === 'y') {
        $returntofeed = '&amp;returntofeed=y';
    } 

    $row=array();
    $nfbi = get_field('newsfeed', 'blockinstance', 'id', $newsfeedid);
    $row[] = new tabobject('edit', "{$CFG->wwwroot}/course/view.php?id=$courseid&amp;instanceid=$nfbi&amp;sesskey={$USER->sesskey}&amp;blockaction=config$returntofeed", get_string('settings', LANGF));
    $row[] = new tabobject('includes', "{$CFG->wwwroot}/blocks/newsfeed/ui/editincludes.php?newsfeedid=$newsfeedid$coursebit$returntofeed", get_string('includes', LANGF));
    $tabs=array();
    $tabs[]=$row;

    $inactive=array();
    if(!$newsfeedid) {
        $inactive[]='includes';
    }

    print '<br />';
    print_tabs($tabs,$current,$inactive);
}

function print_hierarchy_list($includejs=false,$expandroot=true,$hidden=false) {
    global $CFG;
    if($includejs) {
        print "<script type='text/javascript' src='{$CFG->wwwroot}/blocks/newsfeed/ui/newsfeed.js'></script>";
    }
    $hiddenbit=$hidden ? ' style="visibility:hidden"': '';
    $root=feed_system::$inst->get_location_folder(null,'/')->get_id();
    $expandroot=$expandroot ? "xhierarchy_expand($root);":'';
    $strexpand=get_string('iconexpandfolder',LANGF);
    $strcollapse=get_string('iconcollapsefolder',LANGF);
    $strfeed=get_string('iconfeed',LANGF);
    print "
<div class='xhierarchy'>
<div id='o$root' onfocus='this.onclick()' onclick='xhierarchy_select(this.id)' tabindex='0'$hiddenbit><img src='{$CFG->wwwroot}/blocks/newsfeed/ui/collapse.gif' /> /</div>
<ul id='l$root'$hiddenbit></ul>
</div>
<script type='text/javascript'>
xhierarchyuifolder='{$CFG->wwwroot}/blocks/newsfeed/ui';
xhierarchystrexpand='$strexpand';
xhierarchystrcollapse='$strcollapse';
xhierarchystrfeed='$strfeed';
xhierarchypixpath='{$CFG->pixpath}';
xhierarchy_init_root($root);
$expandroot</script>
";
}

// Note that this function is implemented sketchily, but works with our format
function get_single_element_text($parent,$tag) {
    $things=$parent->getElementsByTagName($tag);
    if($things->length==0) {
        return null;
    }
    if(!$things->item(0)->firstChild) {
        return '';
    }
    return $things->item(0)->firstChild->nodeValue;
}

function get_single_element_attribute($parent,$tag,$attribute) {
    $things=$parent->getElementsByTagName($tag);
    if($things->length==0) {
        return null;
    }
    return $things->item(0)->getAttribute($attribute);
}

function get_single_element_attribute_where($parent, $tag, $attribute, $requiredattr, $requiredvalue) {
    $things=$parent->getElementsByTagName($tag);
    if ($things->length!=0) {
        foreach ($things as $thing) {
            if ($thing->getAttribute($requiredattr) === $requiredvalue) {
                return $thing->getAttribute($attribute);
            }
        }
    }
    return null;
}

function display_entry_date($date,$insentence=false) {
    if(function_exists('specially_shrunken_date')) {
        $result=
          htmlspecialchars(specially_shrunken_date($date,$insentence,SSD_EXCEPTMIDNIGHT));
    } else {
        if(date('H:i',$date)==='00:00') {
            $result=userdate($date,get_string('strftimedate'));
        } else {
            $result=userdate($date);
        }
    }
    return str_replace(' ','&nbsp;',$result);
}

function is_sams_auth() {
    global $CFG;
    return defined('AUTH_SAMS') && AUTH_SAMS==$CFG->auth;
}

function print_atom_entry($entry,$short=false) {
    print get_atom_entry($entry,$short);
}
function get_atom_entry($entry,$short=false,$userauthids=null,$showsummary=true) {
    $out='';

    $editinfo='';
    if($authid=get_single_element_text($entry,'authid')) {
        global $CFG;
        if($userauthids!=null) {
            $messageauthids=explode(' ',$authid);
            $ok=false;
            foreach($userauthids as $userauthid) {
                if(in_array($userauthid,$messageauthids)) {
                    $ok=true;
                    break;
                }
            }
            if(!$ok) {
                return $out;
            }
        } else if(is_sams_auth() &&
            !SamsAuth::$inst->match_authids($authid)) {
            return $out;
        }
    }

    // Get entry id (used for #) from the link tag in the atom where it points
    // to this #...
    $entryid = 
        preg_replace('~^.*#~', '', get_single_element_attribute_where(
            $entry, 'link', 'href', 'rel', 'alternate'));
    $out .= '<div class="newsfeed_entry" id="' . $entryid .
        '"><div class="newsfeed_entry_header">';

    // Title
    $title=get_single_element_text($entry,'title');
    $link = get_single_element_attribute_where($entry, 'link', 'href', 'rel', 'related');
    if($title) {
        $middlebit=htmlspecialchars($title);
        if($link) {
            $middlebit='<a href="'.htmlspecialchars($link).'">'.$middlebit.'</a>';
        }
        $out.='<h3 class="newsfeed_title">'.$middlebit.'</h3>';
    }

    // Date
    $published=get_single_element_text($entry,'published');
    $updated=get_single_element_text($entry,'updated');
    if($updated) {
        $out.='<span class="newsfeed_space"> </span>';
        if(!$published || date('Ymd',strtotime($published))==date('Ymd',strtotime($updated))) {
            $out.='<div class="newsfeed_date">'.display_entry_date(strtotime($published)).'</div>';
        } else {
            $a=new stdClass;
            $a->published=display_entry_date(strtotime($published));
            $a->published=display_entry_date(strtotime($published));
            $a->updated=display_entry_date(strtotime($updated),true);
            $out.='<div class="newsfeed_date">'.
                get_string('publishedandupdated',LANGF,$a).'</div>';
        }
    }
    
    $out.='</div><div class="newsfeed_entry_main">';

    // Message
    if($showsummary) {
        $content=get_single_element_text($entry,'content');
        $attachments=preg_match('/<div class="newsfeed_attachments">/',$content);
        if($short) {
            // Summary is content after stripping attachment links...
            $summary=preg_replace('/<(div|ul) class="newsfeed_attachments">.*$/','',$content);
            // ...removing all tags and entities...
            $summary=preg_replace('/<.*?>/s',' ',$summary);
            $summary=html_entity_decode($summary,ENT_QUOTES,'UTF-8');
            // ...tidying white space....
            $summary=trim(preg_replace('/\s+/',' ',$summary));
            $before=$summary;
            // ...stripping to the first full stop after at least one word (space) if present...
            $summary=preg_replace('/(.*? .*?[\!\.\?]).*$/','$1',$summary);
            // ...and reducing to max 20 words with a ...
            $summary=trim(preg_replace('/^((\S+ ){20}).*$/','$1',$summary));
            if($before!=$summary) {
                if(preg_match('/[\.\!\?]$/',$summary)) {
                    $summary.='..';
                } else {
                    $summary.='...';
                }
            }
            // Make it HTML again
            $content=htmlspecialchars($summary);
        }
        $out.='<div class="newsfeed_content">'.$content.'</div>';
        $out.=$editinfo;
    }
    $out.='</div></div>';
    return $out;
}

// Support for old Moodle 1.9 versions that didn't have require_sesskey
if (!function_exists('require_sesskey')) {
    function require_sesskey() {
        if(!confirm_sesskey()) {
            error("Failed to confirm session key"); // crap error or what?
        }
    }
}

function redirect_to_feed($viewfeedid,$courseid) {
    redirect('viewfeed.php?newsfeedid='.$viewfeedid.($courseid ? '&courseid='.$courseid : ''));
    exit;
}

?>