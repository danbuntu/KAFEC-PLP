<?php

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/lib/statslib.php');
    require_once($CFG->dirroot.'/backup/lib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once('lib.php');

    admin_externalpage_setup('reportcomparativereports');
    admin_externalpage_print_header();

    $report     = optional_param('report', STATS_REPORT_YEAR_ON_YEAR, PARAM_INT);
    $time       = optional_param('time', 0, PARAM_INT);
    $numcourses = optional_param('numcourses', 20, PARAM_INT);
    $userid		= optional_param('userid', 0, PARAM_INT);

    require_capability('moodle/site:viewreports', get_context_instance(CONTEXT_SYSTEM, SITEID));  // needed?

    if (empty($CFG->enablestats)) {
        redirect("$CFG->wwwroot/$CFG->admin/settings.php?section=stats", get_string('mustenablestats', 'admin'), 3);
    }

    $course = get_site();
    stats_check_uptodate($course->id);

    $strreports = get_string('reports');
    $strcourseoverview = get_string('category');

    $reportoptions = comparative_reports_stats_get_report_options($course->id,STATS_MODE_COMPARATIVE);

    $tableprefix = $CFG->prefix.'stats_';

    $earliestday = get_field_sql('SELECT timeend FROM '.$tableprefix.'daily ORDER BY timeend');
    $earliestweek = get_field_sql('SELECT timeend FROM '.$tableprefix.'weekly ORDER BY timeend');
    $earliestmonth = get_field_sql('SELECT timeend FROM '.$tableprefix.'monthly ORDER BY timeend');

    if (empty($earliestday)) $earliestday = time();
    if (empty($earliestweek)) $earliestweek = time();
    if (empty($earliestmonth)) $earliestmonth = time();

    $now = stats_get_base_daily();
    $lastweekend = stats_get_base_weekly();
    $lastmonthend = stats_get_base_monthly();

    $timeoptions = stats_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth);

    if (empty($timeoptions)) {
        error(get_string('nostatstodisplay'), $CFG->wwwroot.'/course/view.php?id='.$course->id);
    }

    $sql = 'SELECT DISTINCT id, firstname, lastname, '.sql_concat('firstname', "' '", 'lastname').' AS name
                 FROM '.$CFG->prefix.'user 
                 WHERE deleted = 0 and confirmed = 1 AND firstname <> \'\' AND lastname <> \'\'
                 ORDER BY lastname, firstname ASC';
    if (!$us = get_records_sql($sql)) {
        error('Cannot get list of users');
    }
    $sql = '';

	$users[0] = get_string('chooseuser');
    foreach ($us as $u) {
        $users[$u->id] = fullname($u, true);
    }

    echo '<form action="index.php" method="post">'."\n";
    echo '<fieldset class="invisiblefieldset">';

    $table->width = '*';
    $table->align = array('left','left','left','left','left','left');
    $table->data[] = array(get_string('statsreporttype'),choose_from_menu($reportoptions,'report',$report,'','','',true),
                           get_string('statstimeperiod'),choose_from_menu($timeoptions,'time',$time,'','','',true),
                           '<input type="text" name="numcourses" size="3" maxlength="3" value="'.$numcourses.'" />',
                           get_string('users'),choose_from_menu($users,'userid',$userid,'','','',true),
                           '<input type="submit" value="'.get_string('view').'" /> ') ;

    print_table($table);
    echo '<a>(Some reports will ignore some values entered here)</a>';
    echo '</fieldset>';
    echo '</form>';

    if (!empty($report) && !empty($time)) {
        $param = comparative_reports_stats_get_parameters($time,$report,SITEID,STATS_MODE_COMPARATIVE);
        if (empty($param->nograph)) {
            $param->nograph = 'false';
        }

        if (!empty($param->sql)) {
            $sql = $param->sql;
        } else {
            $sql = 'SELECT '.((empty($param->fieldscomplete)) ? 'id,timeend,' : '').$param->fields
                .' FROM '.$CFG->prefix.'stats_user_'.$param->table.' WHERE '
                .((!empty($userid)) ? ' userid = '.$userid.' AND ' : '')
                .((!empty($roleid)) ? ' roleid = '.$roleid.' AND ' : '')
                . ((!empty($param->stattype)) ? ' stattype = \''.$param->stattype.'\' AND ' : '')
                .' timeend >= '.$param->timeafter
                .' '.$param->extras
                . ((!empty($param->orderby)) ? ' ORDER BY '.$param->orderby : '');
        }
		
		switch ($report) {
			case STATS_REPORT_YEAR_ON_YEAR;
				$numcourses = 12;
				break;
			case STATS_REPORT_ACTIVITY_BY_HOUR;
				$numcourses = 24;
				break;
			case STATS_REPORT_ONE_USERS_ACTIVITY;
				$param->userid = $userid;
				break;
            case STATS_REPORT_COURSE_DISK_USAGE;
                $numcourses = 0;
                $origmaxexecution = get_cfg_var('max_execution_time');
                set_time_limit(0); //Allow extra time for this slow script
                break;
		}
		
        $courses = get_records_sql($sql, 0, $numcourses);

        if (empty($courses)) {
            notify(get_string('statsnodata'));echo '</td></tr></table>';echo '<p>after notify</p>';
        } else {
			switch ($report) {
                case STATS_REPORT_ONE_USERS_ACTIVITY;
                    // Add dates that have 0 hits to show the lack of results for that time period
                    $courses = stats_fix_zeros($courses,$param->timeafter,$param->table,(!empty($param->line2)),(!empty($param->line3)));
                    break;
                case STATS_REPORT_COURSE_DISK_USAGE;
                    // Process each course to get the disk space its folder is using
                    $sitetotalusage = 0;
                    foreach ($courses as $c) {
                        $c->line2 = getdirsizefromid($c->id);
                        $sitetotalusage += $c->line2;
                    }
                    uasort($courses, "comparefunc");
			}
			
			print_heading(format_string(get_string('statsreport'.$report,'report_comparativereports')));
			
            if (empty($CFG->gdversion)) {
                echo '<div class="boxaligncenter">(' . get_string("gdneed") .')</div>';
            } elseif ($param->nograph != 'true') {
                echo '<div class="boxaligncenter"><img alt="'.get_string('comparativereportsoverviewgraph','report_comparativereports').'" src="'.$CFG->wwwroot.'/'.$CFG->admin.'/report/comparativereports/graph'.$report.'.php?time='.$time.'&report='.$report.'&numcourses='.$numcourses.((!empty($userid)) ? '&userid='.$userid : '').'" /></div>';
            }

            $table = new StdClass;
            $table->align = array('left','center','center','center');
            $table->head = array($param->heading1,$param->line1);
            if (!empty($param->line2)) {
                if ($report == STATS_REPORT_COURSE_DISK_USAGE) {
                    $table->head[] = $param->line2.' ('.get_string('total').' '.display_size($sitetotalusage).')';
                } else {
                    $table->head[] = $param->line2;
                }
            }
            if (!empty($param->line3)) {
                $table->head[] = $param->line3;
            }

            foreach ($courses as $c) {
				switch ($report) {
                    case STATS_REPORT_ONE_USERS_ACTIVITY;
                        $c->name = userdate($c->timeend,get_string('strftimedate'),$CFG->timezone);
                        break;
                    case STATS_REPORT_COURSE_DISK_USAGE;
                        $c->line2 = display_size($c->line2);
                        set_time_limit($origmaxexecution);
                }
                $a = array();
                if (empty($param->tableurl) || empty($param->tableidfield)) {
					$a[] = '<a>'.$c->name.'</a>';
                } else {
					$a[] = '<a href="'.$param->tableurl.'?id='.$c->{$param->tableidfield}.'">'.$c->name.'</a>';
				}

                $a[] = $c->line1;
                if (isset($c->line2)) {
                    $a[] = $c->line2;
                }
                if (isset($c->line3)) {
                    $a[] = round($c->line3,2);
                }
                $table->data[] = $a;
            }
            print_table($table);
        }
    }
    admin_externalpage_print_footer();

function getdirsizefromid($id) {
    $sdatatotal = 0;
    $bdatatotal = 0;
    global $CFG;
    $datarootdir = $CFG->dataroot;
    $handle = opendir($datarootdir);
    if ($handle) {
        chdir($datarootdir);
        $coursedir = $id;
        $path = "$datarootdir/$coursedir";
        if (!(is_dir($path))) {
            return '0';
            continue;
        }
        if ($coursedir == '.' || $coursedir == '..') {
            continue;
        } // we know it's a directory now, but lets ignore non-numeric directories as course data is stored in moodledata in a directory named after its course id

        if (!is_numeric($coursedir)) { // site files are stored in /moodledata/$courseid where $courseid is a number - we want to filter out nonsite related directories
            continue;
        }

        if (is_dir("$path/backupdata")) {
            $bdata = display_size(get_directory_size("$path/backupdata"));
            $bdatatotal += (int) get_directory_size("$path/backupdata");
        } else {
            $bdata = display_size((int) 0);
            $bdatatotal += (int) 0;
        }

        if (is_dir($path)) {
            $sdata = display_size(get_directory_size($path));
            $sdatatotal += (int) get_directory_size($path);
        } else {
            $sdata = display_size((int) 0);
            $sdatatotal += (int) 0;
        }
    } else {
        echo "Failed to open moodledata<br />";
    }

    //return display_size($sdatatotal);
    return $sdatatotal+$bdatatotal;
}

function comparefunc($a, $b) {
    if($a->line2>$b->line2) {
        return -1;
    } elseif ($b->line2>$a->line2) {
        return 1;
    } else {
        return 0;
    }
}
?>
