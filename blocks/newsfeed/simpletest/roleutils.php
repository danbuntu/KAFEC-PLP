<?php
/**
 * TODO Describe purpose of file or single class here.
 *
 * @copyright &copy; 2006 The Open University
 * @author D.A.Woolhead@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package TODO
 */

/**
 * Removes a role from a user.
 * @param int $userid Moodle userid
 * @param string $rolename ROLE_xx constant
 * @throws Exception if they don't have that role
 */
function newsfeed_remove_test_role($nf,$userid,$approver=false) {
    $shortname = $approver ? 'newsfeedtestapprover' : 'newsfeedtestposter';
    if (!($role = get_record('role','shortname', $shortname))) {
        throw new Exception("Role shortname=$shortname does not exist",
            EXN_NEWSFEED_NOTGOTROLE);
    }

    // Get context for this newsfeed
    $context = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());

    // Unassign Role
    if (!($unassign = role_unassign($role->id, $userid, 0, $context->id))) {
        throw new Exception("Unable to unassign role $role->name from user $userid for newsfeed $context->instanceid",
            EXN_NEWSFEED_ROLENOTSET);
    }
}

    /**
     * Checks feed roles (requires DB access first time).
     * @param int $userid Moodle userid
     * @param string $rolename ROLE_xx constant
     * @return bool True if given user has the specified role on this feed
     */
    function has_feed_capability($userid, $rolename, $nfblockinstance, $roles) {
        $nfcontext = get_context_instance(CONTEXT_BLOCK, $nfblockinstance);
        if(!$userid) {
            global $USER;
            $userid=$USER->id;
            if(has_capability('block/newsfeed:manage', $nfcontext)) return true; 
        }
/* revert to previous method as cannot clear capability cache
        if ($rolename == internal_news_feed::ROLE_POSTER) {
            return has_capability('block/newsfeed:post', $nfcontext, $userid); 
        } elseif ($rolename == internal_news_feed::ROLE_APPROVER) {
            return has_capability('block/newsfeed:approve', $nfcontext, $userid); 
        } else {
            return false;
        }
*/
        foreach($roles as $roleinfolist) {
            foreach($roleinfolist as $roleinfo) {
                if($roleinfo->get_user_id()==$userid && $roleinfo->get_role_name()==$rolename) {
                    return true;
                }
            } 
        }
        return false;
    }
    
/**
 * Adds a poster/approver to the feed. This involves creating a special 'test poster/approver'
 * role, if not already present. Unlike the default-created role, this role only has post
 * or approve capability, not both.
 * @param news_feed $nf Newsfeed to add approver  
 * @param int $userid User ID to add
 */
function newsfeed_add_test_role($nf,$userid,$approver=false) {
    $shortname = $approver ? 'newsfeedtestapprover' : 'newsfeedtestposter';
    
    if (!($rid = get_field('role','id','shortname', $shortname))) {
        // Create test role
        $name = 'TEST: Newsfeed '.($approver?'approver':'poster');
        $description = 'Role used only during testing. Should not exist on real system.';
        $rid = create_role($name, $shortname, $description);       
        $capname=$approver ?'block/newsfeed:approve' :  'block/newsfeed:post';
        $sitecontext = get_context_instance(CONTEXT_SYSTEM, SITEID);
        assign_capability($capname, CAP_ALLOW, $rid, $sitecontext->id);        
    }

    // Get context for this newsfeed
    $context = get_context_instance(CONTEXT_BLOCK, $nf->get_blockinstance());

    // Assign Role
    if (!($assign = role_assign($rid, $userid, 0, $context->id))) {
        throw new Exception("Unable to assign role $role->name to user $userid for newsfeed $context->instanceid",
            EXN_NEWSFEED_ROLENOTSET);
    }
}    
?>
