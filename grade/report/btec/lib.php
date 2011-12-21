<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * File in which the btec_report class is defined                      (1)
 *
 * Every DUE gradable item in the course with outocmes assigned to it
 * will be represented in this report. As it was designed for BTEC
 * courses certain assumptions are made.
 * Every outcome has in its name P, M or D followed by a number, with
 * no space. Px items will make up the 100% for each item. 100% will
 * show as a P. If all P and M outcomes are 100% it will show as M.
 * If all P, M and D outcomes are 100% it will show as D.
 * The grading scale for the outcomes should have one negative item
 * and only be achieved by the top item, e.g., Fail, Partial, Pass.    (2)
 *
 * @package   grade/report/btec                                        (3)
 * @copyright 2009 Red Morris                                          (4)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (5)
 */

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');

/**
 * Class providing an API for the btec report building and displaying.
 * @uses grade_report
 * @package grade/report/btec
 */
class grade_report_btec extends grade_report {

    /**
     * The user.
     * @var object $user
     */
    var $user;

    /**
     * A flexitable to hold the data.
     * @var object $table
     */
    var $table;

    /**
     * show student ranks
     */
    var $showrank;

    /**
     * Holds the id of the group to filter by
     * @var integer
     */
    var $groupid;

    /**
     * The id of the grade_item by which this report will be sorted.
     * @var int $sortitemid
     */
    var $sortitemid;

    /**
     * The order by which this report will be sorted.
     * 0 = ascending
     * @var int $sortorder
     */
    var $sortorder;

    /**
     * The html to print the key. Provided this way to prevent
     * re-querying the database
     * @var string $keyhtml
     */
    var $keyhtml;

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $userid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     */
    function grade_report_btec($userid, $gpr, $context) {
        global $CFG, $COURSE;
        parent::grade_report($COURSE->id, $gpr, $context);

//        $this->showrank = grade_get_setting($this->courseid, 'report_btec_showrank', !empty($CFG->grade_report_btec_showrank));

        // get the user (for full name)
        // $this->user = get_record('user', 'id', $userid);


        // base url for sorting by first/last name
        $this->baseurl = $CFG->wwwroot.'/grade/report/btec/index.php?id='.$userid;
        $this->pbarurl = $this->baseurl;
    }

    function get_html_for_all_users() {
        function check_btec_outcome($outcomename, $identifier) {
            $blnValid = false;
            $strOutcomename = $outcomename;
            $strPMDLocation = strpos($strOutcomename, $identifier);
            if ($strPMDLocation !== false) {
                $strOutcomename = substr($strOutcomename,$strPMDLocation + 1);
                if (is_numeric($strOutcomename)) {
                    return true;
                } else {
                    $strPMDLocation = false;
                    $strPMDLocation = strpos($strOutcomename, ' ');
                    if ($strPMDLocation !== false) {
                        if (is_numeric(substr($strOutcomename, 0, $strPMDLocation - 1))) {
                            // The name contains the identifier followed by a number
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        global $CFG;

        $this->group = 0;
        // Check if there's a group selected so we can pass it on when sorting
        $currentgroup = groups_get_course_group($this->course, true);
        if ($currentgroup !== false && $currentgroup != 0) {
            $this->group = $currentgroup ;

            // Get a list of all the groupings the current group belongs to
            $sql = 'SELECT groupingid FROM '.$CFG->prefix.'groupings_groups WHERE groupid = '.$this->group;
            $groups = get_records_sql($sql);
            $groupsstr = '(';
            foreach ($groups as $group) { // Create a string to use in later SQL query
                $groupsstr .= $group->groupingid.',';
            }
            $groupsstr .= '0)';
            unset($groups);

            // Get all the assignments with outcomes that are visible to this group
            $sql = 'SELECT CONCAT(gi.iteminstance,\'-\',gi.outcomeid) AS uniqueindex, gi.id as outcomeid, gi.itemname AS outcomename, gi.iteminstance AS itemid, a.name AS itemname, cm.id AS itemcontext, s.scale, gi.grademax, a.timedue
                    FROM '.$CFG->prefix.'grade_items gi
                    JOIN '.$CFG->prefix.'scale s ON gi.scaleid = s.id
                    JOIN '.$CFG->prefix.'assignment a ON gi.iteminstance = a.id
                    JOIN '.$CFG->prefix.'course_modules cm ON gi.iteminstance = cm.instance
                    WHERE gi.outcomeid IS NOT NULL AND gi.courseid = '.$this->courseid.' AND cm.course = '.$this->courseid.' AND cm.visible = 1 AND cm.groupingid IN '.$groupsstr.'
                    ORDER BY gi.iteminstance, gi.id';
            $itemsandoutcomes = get_records_sql($sql);
        } else {
            // Get all the assignments with outcomes that are visible
            $sql = 'SELECT CONCAT(gi.iteminstance,\'-\',gi.outcomeid) AS uniqueindex, gi.id as outcomeid, gi.itemname AS outcomename, gi.iteminstance AS itemid, a.name AS itemname, cm.id AS itemcontext, s.scale, gi.grademax, a.timedue, cm.groupmembersonly, cm.groupingid
                    FROM '.$CFG->prefix.'grade_items gi
                    JOIN '.$CFG->prefix.'scale s ON gi.scaleid = s.id
                    JOIN '.$CFG->prefix.'assignment a ON gi.iteminstance = a.id
                    JOIN '.$CFG->prefix.'course_modules cm ON gi.iteminstance = cm.instance
                    WHERE gi.outcomeid IS NOT NULL AND gi.courseid = '.$this->courseid.' AND cm.course = '.$this->courseid.' AND cm.visible = 1
                    ORDER BY gi.iteminstance, gi.id';
            $itemsandoutcomes = get_records_sql($sql);
        }
        $outcomesstr = '(';
        foreach ($itemsandoutcomes as $outcome) { // Create a string to use in later SQL query
            $outcomesstr .= $outcome->outcomeid.',';
        }
        if ($outcomesstr == '(') {
            // No assignments with outcomes found
            print_error('nooutcomedata', 'gradereport_btec', $CFG->wwwroot.'/course/view.php?id='.$this->courseid);
        }
        $outcomesstr = substr($outcomesstr, 0, strlen($outcomesstr)-1).')';
        
        // Get all of the grades against the outcomes we found in the previous query
        $sql = 'SELECT CONCAT(itemid,\'-\',userid) AS id, itemid, userid, finalgrade
                FROM '.$CFG->prefix.'grade_grades
                WHERE itemid IN '.$outcomesstr.'
                ORDER BY itemid';
        $grades = get_records_sql($sql);

        // Start of HTML

        // Heading row
        // Create strings for the sorting labels at the column heads
        $strsortasc   = $this->get_lang_string('sortasc', 'grades');
        $strsortdesc  = $this->get_lang_string('sortdesc', 'grades');

        // If the item being clicked is the current item, reverse the order
        if ($this->sortitemid == 'firstname') {
            if ($this->sortorder == 0) {
                $firstarrow = print_arrow('up', $strsortdesc, true);
                $sortpart1 = '&sortitemid=firstname&sortorder=1';
                $SQLsort = ' ORDER BY u.firstname ASC, u.lastname ASC';
            } else {
                $firstarrow = print_arrow('down', $strsortasc, true);
                $sortpart1 = '&sortitemid=firstname&sortorder=0';
                $SQLsort = ' ORDER BY u.firstname DESC, u.lastname DESC';
            }
            $secondarrow = '';
            $sortpart2 = '&sortitemid=lastname&sortorder=0';
        } else {
            $firstarrow = '';
            $sortpart1 = '&sortitemid=firstname&sortorder=0';
        }
        if ($this->sortitemid == 'lastname') {
            if ($this->sortorder == 0) {
                $secondarrow = print_arrow('up', $strsortdesc, true);
                $sortpart2 = '&sortitemid=lastname&sortorder=1';
                $SQLsort = ' ORDER BY u.lastname ASC, u.firstname ASC';
            } else {
                $secondarrow = print_arrow('down', $strsortasc, true);
                $sortpart2 = '&sortitemid=lastname&sortorder=0';
                $SQLsort = ' ORDER BY u.lastname DESC, u.firstname DESC';
            }
            $firstarrow = '';
            $sortpart1 = '&sortitemid=firstname&sortorder=0';
        } else {
            $secondarrow = '';
            $sortpart2 = '&sortitemid=lastname&sortorder=0';
        }

        // Get a list of all the users in the course/group
        if ($this->group > 0) {
            $sql = 'SELECT u.id, u.firstname, u.lastname, u.picture, u.imagealt, '.$this->courseid.' As itemid
                    FROM '.$CFG->prefix.'groups_members gm
                    JOIN '.$CFG->prefix.'user u ON gm.userid = u.id
                    JOIN '.$CFG->prefix.'role_assignments ra ON u.id = ra.userid
                    WHERE gm.groupid = '.$this->group.' AND ra.roleid = 5 AND ra.contextid = (SELECT id FROM mdl_context WHERE instanceid = '.$this->courseid.' AND contextlevel = 50)'.$SQLsort;
        } else {
            $sql = 'SELECT u.id, u.firstname, u.lastname, u.picture, u.imagealt, '.$this->courseid.' As itemid
                    FROM '.$CFG->prefix.'user u
                    JOIN '.$CFG->prefix.'role_assignments ra ON u.id = ra.userid
                    WHERE ra.roleid = 5 AND ra.contextid = (SELECT id FROM mdl_context WHERE instanceid = '.$this->courseid.' AND contextlevel = 50)'.$SQLsort;
        }
        $users = get_records_sql($sql);

        $headerhtml = '<div id="user-grades" class="gradeparent">
                        <table><tr><td>
                        <table class="gradestable"><tr><th class="header c0">'
                        .$firstarrow.'<a href="'.$this->baseurl.$sortpart1.'">'.$this->get_lang_string('firstname').'</a> /
                        <a href="'.$this->baseurl.$sortpart2.'">'.$this->get_lang_string('lastname').'</a>'.$secondarrow;

        $this->keyhtml = '<div class="key"><strong>'.get_string('key', 'gradereport_btec').'</strong>
                           <table>
                            <tr><td></td><td></td></tr>
                            <tr><td class="btecscoreblock"><div class="pass">P</div></td><td> Pass</td></tr>
                            <tr><td class="btecscoreblock"><div class="merit">M</div></td><td> Merit</td></tr>
                            <tr><td class="btecscoreblock"><div class="distinction">D</div></td><td> Distinction</td></tr>
                            <tr><td class="btecscoreblock"><div class="partial">75%</div></td><td> Graded but not passed</td></tr>
                            <tr><td class="btecscoreblock"><div class="notsubmitted"></div></td><td> Overdue</td></tr>
                            <tr><td class="btecscoreblock"><div class="availablebutnotdue"></div></td><td> Available but not due</td></tr>
                          </table>
                          <br />
                          <strong>'.get_string('activities', 'gradereport_btec').'</strong>';

        $i = 0;
        $lastid = 0;
        foreach ($itemsandoutcomes as $item) {
            if ($item->itemid != $lastid) {
                $lastid = $item->itemid;
                $i++;
                $headerhtml .= '<th class="header c'.$i.'" title="'.$item->itemname.'">
                                <a href="'.$CFG->wwwroot.'/mod/assignment/view.php?id='.$item->itemcontext.'">'.
                                '<img src="'.$CFG->pixpath.'/mod/assignment/icon.gif"> '.$i.'</a>
                                </th>';

                $this->keyhtml .= '<br /><a href="'.$CFG->wwwroot.'/mod/assignment/view.php?id='.$item->itemcontext.'">
                                <img src="'.$CFG->pixpath.'/mod/assignment/icon.gif"> '.$i.' - '.$item->itemname.'</a>';
            }
        }
        $headerhtml .= '<th class="header c'.($i+1).' finalgrade">'.get_string('finalgrade', 'grades').'</th></tr>';
        $this->keyhtml .= '</div>';

        $row = 1;
        $odd = true;
        $displayitems = array();
        $tablehtml = '';

        foreach ($users as $user) {
            if($odd) {
                $oddeven = ' odd';
            } else {
                $oddeven = ' even';
            }
            $tablehtml .= '<tr class="r'.$row.$oddeven.'"><th class="header c0 user" scope="row"><div class="userpic">'.print_user_picture($user, $this->course->id, NULL, 0, true).
                                '</div><a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$this->course->id.'">'.$user->firstname.' '.$user->lastname.'</a></th>';
            $row++;
            $odd = !$odd;

            $lastid = 0;
            unset($displayitems);
            foreach ($itemsandoutcomes as $item) {
                if ($item->itemid != $lastid) {
                    $lastid = $item->itemid;
                    $displayitems[] = '';
                    $MaxScore = 0;
                    $FinalScore = 0;
                    $Class = 'partial';
                    $Tooltip = '';
                    $meritachieved = -1;
                    $distinctionachieved = -1;
                    $finalgrade = -1;

                    $notapplicable = false;
                    if($this->group == 0 && $item->groupmembersonly == '1') {
                        // All users, so there may be people with assignments they can't see
                        if (groups_get_all_groups($this->course->id, $user->id, $item->groupingid) === false) {
                            $notapplicable = true;
                        }
                    }
                }

                if(!$notapplicable) {
                    $scales = explode(',', $item->scale);

                    if (check_btec_outcome($item->outcomename, 'M') == true) { // It is a Merit item
                        $ismerititem = true;
                        $isdistinctionitem = false;
                    } elseif (check_btec_outcome($item->outcomename, 'D') == true) { // It is a Distinction item
                        $isdistinctionitem = true;
                        $ismerititem = false;
                    } else {
                        $ismerititem = false;
                        $isdistinctionitem = false;
                    }

                    if(isset($grades[$item->outcomeid.'-'.$user->id]->finalgrade)) { // Try to find a matching grade
                        $gradefound = true;
                        if($grades[$item->outcomeid.'-'.$user->id]->finalgrade > 1 && !$ismerititem && !$isdistinctionitem) {
                            $FinalScore++;
                        }
                        if (isset($displayitems[count($displayitems)-1]["Tooltip"])) {
                            $Tooltip = $displayitems[count($displayitems)-1]["Tooltip"];
                        }
                        $Tooltip = $Tooltip.'&lt;div class=&quot;gradeitem&quot;&gt;'.$item->outcomename.' = &lt;strong&gt;'.$scales[round($grades[$item->outcomeid.'-'.$user->id]->finalgrade)-1].'&lt;/strong&gt;&lt;/div&gt;';
                    } else { // Failled to find a match, but we still need to report the grade
                        $gradefound = false;
                        if (isset($displayitems[count($displayitems)-1]["Tooltip"])) {
                            $Tooltip = $displayitems[count($displayitems)-1]["Tooltip"];
                        }
                        $Tooltip = $Tooltip.'&lt;div class=&quot;gradeitem&quot;&gt;'.$item->outcomename.' = &lt;strong&gt;'.$scales[0].'&lt;/strong&gt;&lt;/div&gt;';
                    }

                    if ($ismerititem) {
                        if ($gradefound && (round($grades[$item->outcomeid.'-'.$user->id]->finalgrade) > 1) && ($meritachieved != 0)) {
                            $meritachieved = 1;
                        } else {
                            $meritachieved = 0;
                            $distinctionachieved = 0;
                        }
                    } elseif ($isdistinctionitem) {
                        if ($gradefound && (round($grades[$item->outcomeid.'-'.$user->id]->finalgrade) > 1) && ($distinctionachieved != 0)) {
                            $distinctionachieved = 1;
                        } else {
                            $distinctionachieved = 0;
                        }
                    } else {
                        $MaxScore++;
                    }

                    if ($distinctionachieved == 1) {
                        $Class = 'distinction';
                        $Text = 'D';
                    } elseif ($meritachieved == 1) {
                        $Class = 'merit';
                        $Text = 'M';
                    } elseif($FinalScore == $MaxScore) {
                        $Class = 'pass';
                        $Text = 'P';
                    } elseif($FinalScore == 0) {
                        if($item->timedue < time()) {
                            $Class = 'notsubmitted';
                        } else {
                            $Class = 'availablebutnotdue';
                        }
                        $Text = '';
                    } else {
                        $Class = 'partial';
                        $Text = round(($FinalScore/$MaxScore)*100).'%';
                    }

                    $displayitems[count($displayitems)-1] = array("ItemName" => $item->itemname,
                                                            "Class" => $Class,
                                                            "MaxScore" => $MaxScore,
                                                            "FinalScore" => $FinalScore,
                                                            "Text" => $Text,
                                                            "Tooltip" => $Tooltip);
                } else {
                    $displayitems[count($displayitems)-1] = array("ItemName" => $item->itemname,
                                                            "Class" => 'hiddentothisuser',
                                                            "MaxScore" => 0,
                                                            "FinalScore" => 0,
                                                            "Text" => get_string('nomode','grades'),
                                                            "Tooltip" => '');
                }
            }

            // Print the users grades
            foreach ($displayitems as $displayitem) {
                $TooltipUserAndItem = ' cell" title="&lt;div class=&quot;fullname&quot;&gt;'.$user->firstname.' '.$user->lastname.'&lt;/div&gt;
                             - &lt;div class=&quot;itemname&quot;&gt;'.$displayitem["ItemName"].'&lt;/div&gt;';

                switch ($displayitem["Class"]) {
                    case 'notsubmitted';
                        $displayitem["Tooltip"] = '';
                        $finalgrade = '0';
                        break;
                    case 'availablebutnotdue';
                    case 'hiddentothisuser';
                        $TooltipUserAndItem = '';
                        $displayitem["Tooltip"] = '';
                        break;
                    case 'distinction';
                        if ($finalgrade == -1) { // First item so it is the best at this stage
                            $finalgrade = 3;
                        }
                        break;
                    case 'merit';
                        if ($finalgrade == -1 || $finalgrade > 2) {
                            $finalgrade = 2;
                        }
                        break;
                    case 'pass';
                        if ($finalgrade == -1 || $finalgrade > 1) {
                            $finalgrade = 1;
                        }
                        break;
                    default;
                        $finalgrade = 0;
                }

                $tablehtml .= '<td class="btecscoreblock grade'.$TooltipUserAndItem.$displayitem["Tooltip"].'"><div class="'.$displayitem["Class"].'">'.$displayitem["Text"].'</div></td>';
            }

            switch ($finalgrade) {
                case 3;
                    $finalgrade = 'Distinction';
                    break;
                case 2;
                    $finalgrade = 'Merit';
                    break;
                case 1;
                    $finalgrade = 'Pass';
                    break;
                case 0;
                    $finalgrade = 'Not Yet Achieved';
                    break;
                default;
                    $finalgrade = get_string('nograde');
            }

            $tablehtml .= '<td class="finalgrade"><div class="finalgrade">'.$finalgrade.'</div></td>';
            $tablehtml .= '</tr>';
        }

        return $headerhtml.$tablehtml.'<div>';
}

    function get_html_for_this_user() {
        function check_btec_outcome($outcomename, $identifier) {
            $blnValid = false;
            $strOutcomename = $outcomename;
            $strPMDLocation = strpos($strOutcomename, $identifier);
            if ($strPMDLocation !== false) {
                $strOutcomename = substr($strOutcomename,$strPMDLocation + 1);
                if (is_numeric($strOutcomename)) {
                    return true;
                } else {
                    $strPMDLocation = false;
                    $strPMDLocation = strpos($strOutcomename, ' ');
                    if ($strPMDLocation !== false) {
                        if (is_numeric(substr($strOutcomename, 0, $strPMDLocation - 1))) {
                            // The name contains the identifier followed by a number
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        global $CFG, $USER;

        $tablehtml = '';

        // Get a list of the groupings that every group this user is a memer of belong to
        $sql = 'SELECT gg.groupingid
                FROM '.$CFG->prefix.'groups_members gm
                JOIN '.$CFG->prefix.'groups g ON gm.groupid = g.id
                RIGHT JOIN '.$CFG->prefix.'groupings_groups gg ON g.id = gg.groupid
                WHERE gm.userid = '.$USER->id.' AND g.courseid = '.$this->courseid.' AND gg.groupingid IS NOT NULL';
        $groupings = get_records_sql($sql);

        $groupsstr = '(';
        foreach ($groupings as $grouping) { // Create a string to use in later SQL query
            $groupsstr .= $grouping->groupingid.',';
        }
        $groupsstr .= '0)';
        unset($groups);
      
        // Get all the assignments with outcomes that are visible to this group
        $sql = 'SELECT CONCAT(gi.iteminstance,\'-\',gi.outcomeid) AS uniqueindex, gi.id as outcomeid, gi.itemname AS outcomename, gi.iteminstance AS itemid, a.name AS itemname, cm.id AS itemcontext, s.scale, gi.grademax, a.timedue
                FROM '.$CFG->prefix.'grade_items gi
                JOIN '.$CFG->prefix.'scale s ON gi.scaleid = s.id
                JOIN '.$CFG->prefix.'assignment a ON gi.iteminstance = a.id
                JOIN '.$CFG->prefix.'course_modules cm ON gi.iteminstance = cm.instance
                WHERE gi.outcomeid IS NOT NULL AND gi.courseid = '.$this->courseid.' AND cm.course = '.$this->courseid.' AND cm.visible = 1 AND cm.groupingid IN '.$groupsstr.'
                ORDER BY gi.iteminstance, gi.id';
        $itemsandoutcomes = get_records_sql($sql);
        
        $outcomesstr = '(';
        foreach ($itemsandoutcomes as $outcome) { // Create a string to use in later SQL query
            $outcomesstr .= $outcome->outcomeid.',';
        }
        if ($outcomesstr == '(') {
            // No assignments with outcomes found
            print_error('nooutcomedata', 'gradereport_btec', $CFG->wwwroot.'/course/view.php?id='.$this->courseid);
        }
        $outcomesstr = substr($outcomesstr, 0, strlen($outcomesstr)-1).')';

        // Get all of the grades against the outcomes we found in the previous query
        $sql = 'SELECT itemid, finalgrade
                FROM '.$CFG->prefix.'grade_grades
                WHERE itemid IN '.$outcomesstr.' AND userid = '.$USER->id.'
                ORDER BY itemid';
        $grades = get_records_sql($sql);

        $tablehtml = '<div id="user-grades" class="gradeparent">
                        <table><tr><td>
                        <table class="gradestable"><tr><th class="header c0">'.get_string('modulename','assignment').'</th>
                            <th class="header c1">'.get_string('grade').'</th>
                            <th class="header c2">Grade Breakdown</th></tr>';

        $lastid = 0;
        foreach ($itemsandoutcomes as $item) {
            if ($item->itemid != $lastid) {
                $lastid = $item->itemid;
                $displayitems[] = '';
                $MaxScore = 0;
                $FinalScore = 0;
                $Class = 'partial';
                $Details = '';
                $meritachieved = -1;
                $distinctionachieved = -1;
                $finalgrade = -1;
            }

            $scales = explode(',', $item->scale);

            if (check_btec_outcome($item->outcomename, 'M') == true) { // It is a Merit item
                $ismerititem = true;
                $isdistinctionitem = false;
            } elseif (check_btec_outcome($item->outcomename, 'D') == true) { // It is a Distinction item
                $isdistinctionitem = true;
                $ismerititem = false;
            } else {
                $ismerititem = false;
                $isdistinctionitem = false;
            }

            if(isset($grades[$item->outcomeid]->finalgrade)) { // Try to find a matching grade
                $gradefound = true;
                if($grades[$item->outcomeid]->finalgrade > 1 && !$ismerititem && !$isdistinctionitem) {
                    $FinalScore++;
                }
                if (isset($displayitems[count($displayitems)-1]["Details"])) {
                    $Details = $displayitems[count($displayitems)-1]["Details"];
                }
                $Details = $Details.'<div class="gradeitem">'.$item->outcomename.' = <strong>'.$scales[round($grades[$item->outcomeid]->finalgrade)-1].'</strong></div>';

            } else { // Failled to find a match, but we still need to report the grade
                $gradefound = false;
                if (isset($displayitems[count($displayitems)-1]["Details"])) {
                    $Details = $displayitems[count($displayitems)-1]["Details"];
                }
                $Details = $Details.'<div class="gradeitem">'.$item->outcomename.' = <strong>'.$scales[0].'</strong></div>';
            }

            if ($ismerititem) {
                if ($gradefound && (round($grades[$item->outcomeid]->finalgrade) > 1) && ($meritachieved != 0)) {
                    $meritachieved = 1;
                } else {
                    $meritachieved = 0;
                }
            } elseif ($isdistinctionitem) {
                if ($gradefound && (round($grades[$item->outcomeid]->finalgrade) > 1) && ($distinctionachieved != 0)) {
                    $distinctionachieved = 1;
                } else {
                    $distinctionachieved = 0;
                }
            } else {
                $MaxScore++;
            }

            if ($distinctionachieved == 1) {
                $Class = 'distinction';
                $Text = 'Distinction';
            } elseif ($meritachieved == 1) {
                $Class = 'merit';
                $Text = 'Merit';
            } elseif($FinalScore == $MaxScore) {
                $Class = 'pass';
                $Text = 'Pass';
            } elseif($FinalScore == 0) {
                if($item->timedue < time()) {
                    $Class = 'notsubmitted';
                    $Text = 'Not Submitted';
                } else {
                    $Class = 'availablebutnotdue';
                    $Text = get_string('notsubmittedyet', 'assignment');
                }
            } else {
                $Class = 'partial';
                $Text = round(($FinalScore/$MaxScore)*100).'%';
            }

            $displayitems[count($displayitems)-1] = array("ItemName" => $item->itemname,
                                                    "Context" => $item->itemcontext,
                                                    "Class" => $Class,
                                                    "MaxScore" => $MaxScore,
                                                    "FinalScore" => $FinalScore,
                                                    "Text" => $Text,
                                                    "Details" => $Details);
        }

        // Print the users grades
        $oddeven = 'even';
        foreach ($displayitems as $displayitem) {
            if($oddeven == 'even') {
                $oddeven = 'odd';
            } else {
                $oddeven = 'even';
            }

            switch ($displayitem["Class"]) {
                case 'notsubmitted':
                    $finalgrade = '0';
                    break;
                case 'availablebutnotdue':
                case 'hiddentothisuser':
                    $displayitem['Class'] = 'nograde';
                    break;
                case 'distinction':
                    if ($finalgrade == -1) { // First item so it is the best at this stage
                        $finalgrade = 3;
                    }
                    break;
                case 'merit':
                    if ($finalgrade == -1 || $finalgrade > 2) {
                        $finalgrade = 2;
                    }
                    break;
                case 'pass':
                    if ($finalgrade == -1 || $finalgrade > 1) {
                        $finalgrade = 1;
                    }
                    break;
                default:
                    $finalgrade = 0;
            }

            $tablehtml .= '<tr class="studentview '.$oddeven.'">
                            <td><a href="'.$CFG->wwwroot.'/mod/assignment/view.php?id='.$displayitem["Context"].'">'.
                                '<img src="'.$CFG->pixpath.'/mod/assignment/icon.gif"> '.$displayitem["ItemName"].'</a></td>
                            <td class="'.$oddeven.'"><div class="studentviewgrade '.$displayitem['Class'].'">'.$displayitem["Text"].'</div></td>
                            <td><div class="bd studentviewbreakdown">'.$displayitem["Details"].'</div></td>
                        </tr>';
        }

        switch ($finalgrade) {
            case 3;
                $finalgrade = 'Distinction';
                break;
            case 2;
                $finalgrade = 'Merit';
                break;
            case 1;
                $finalgrade = 'Pass';
                break;
            case 0;
                $finalgrade = 'Not Yet Achieved';
                break;
            default;
                $finalgrade = get_string('nograde');
        }

        $tablehtml .= '<tr class="studentview">';
        $tablehtml .= '<th class="header">'.get_string('finalgrade', 'grades').'</th>';
        $tablehtml .= '<td><div class="studentviewgrade finalgrade">'.$finalgrade.'</div></td>';
        $tablehtml .= '<th class="header"></th>';
        $tablehtml .= '</tr>';

        return $tablehtml.'</div>';
    }

    function get_closing_html() {
        $closinghtml = '</tr></table></div>';
        return $closinghtml;
    }
}

?>