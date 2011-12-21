<?php
/**
 * Confirm and move newsfeed block
 *
 * @copyright &copy; 2008 The Open University
 * @author d.a.woolhead@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */

global $CFG, $USER;

require_once(dirname(__FILE__).'/../../config.php');

// Pick up course id (the one the block is being moved from)
$id = optional_param('id', 0, PARAM_INT);
$url = $CFG->wwwroot.'/course/view.php?id='.$id;

// Ensure have capability to move blocks
if(!has_capability('moodle/site:manageblocks', get_context_instance(CONTEXT_SYSTEM, SITEID))) {
    print_error('nopermissions', 'error', $url, get_string('move_block', 'block_newsfeed'));
}

// Check if want to move block (otherwise confirming/canceling move)
$submitmove = optional_param('submitmove', null, PARAM_ALPHA);
if (isset($submitmove)) {

    // Want to move block - validate course shortname
    $shortname = stripslashes(optional_param('courseshortname', '', PARAM_RAW));
    if (empty($shortname) || !($newid = get_field('course', 'id', 'shortname', $shortname))) {
        $url = $PAGE->url_get_full(array('instanceid' => $this->instance->id,
                                         'sesskey' => $USER->sesskey,
                                         'blockaction' => 'blockaction',
                                         'returntofeed' => optional_param('returntofeed', 'n', PARAM_RAW)));
        print_error('shortname_not_found', 'block_newsfeed', $url, $shortname);
    }

    // Display confirmation page
    $url = $CFG->wwwroot.'/blocks/newsfeed/move_newsfeed.php';
    $PAGE = page_create_object(PAGE_COURSE_VIEW, $id);
    $PAGE->print_header('');
    print_box_start();
    print '<div class="boxconfirm">';
    print '<p>'.get_string('are_you_sure', 'block_newsfeed', $shortname).'</p>';
    print '<form action="'.$CFG->wwwroot.'/blocks/newsfeed/move_newsfeed.php'.'" method="post">';
    print
        '<input type="hidden" name="id" value="'.$id.'" />'.
        '<input type="hidden" name="instanceid" value="'.$this->instance->id.'" />'.
        '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />'.
        '<input type="hidden" name="blockaction" value="'.'blockaction'.'" />'.
        '<input type="hidden" name="returntofeed" value="'.optional_param('returntofeed', 'n', PARAM_RAW).'" />'.
        '<input type="hidden" name="confirmid" value="'.$newid.'" />'.
        '<input type="submit" name="confirmmove" value="'.get_string('move_block', 'block_newsfeed').'"/> '.
        '<input type="submit" name="cancel" value="'.get_string('cancel').'"/>';
    print '</form>';
    print '</div>';
    print_box_end();
    print_footer();

} else {

    // Check for move confirmation
    $confirmmove = optional_param('confirmmove', null, PARAM_ALPHA);
    if (!isset($confirmmove)) {
        redirect($url);
    }

    // Check destination course exists
    $destinationid = optional_param('confirmid', 0, PARAM_INT);
    if (!$destinationid || !(record_exists('course', 'id', $destinationid))) {
        print_error('course_not_found', 'block_newsfeed', $url);
    }

    // Check block instance
    $biid = optional_param('instanceid', 0, PARAM_INT);
    if (!$biid ||
        !($bi = get_record('block_instance', 'id', $biid, '', '', '', '', 'id, pageid, position, weight, pagetype'))) {
        print_error('block_not_found', 'block_newsfeed', $url);
    }

    // Get required weight for destination course
    $sql = 'SELECT 1, max(weight) + 1 AS nextfree' .
           ' FROM '.$CFG->prefix.'block_instance' .
           ' WHERE pageid = '.$destinationid.
           ' AND pagetype = \''.$bi->pagetype.'\'' .
           ' AND position = \''. $bi->position .'\'';
    $weight = get_record_sql($sql);

    // Close the weight gap we'll leave behind
    $sql = 'UPDATE '. $CFG->prefix .'block_instance SET weight = weight - 1 '.
                    'WHERE pagetype = \''. $bi->pagetype.
                    '\' AND pageid = '. $bi->pageid .
                    ' AND position = \'' .$bi->position.
                    '\' AND weight > '. $bi->weight;
    execute_sql($sql,false);

    // Update block instance
    // Allow moving to same course - useful for testing
    if ($bi->pageid == $destinationid) {
        $weight->nextfree--;
    }
    $bi->pageid = $destinationid;
    $bi->weight = empty($weight->nextfree) ? 0 : $weight->nextfree;
    update_record('block_instance', $bi);

    // Move newsfeed contexts 
    $context = get_context_instance(CONTEXT_BLOCK, $biid);
    $newparent = get_context_instance(CONTEXT_COURSE, $destinationid);
    context_moved($context, $newparent);

    // Go back to original course home page
    redirect($url);
}
exit();
?>

