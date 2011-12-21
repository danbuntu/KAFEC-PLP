<?php // $Id: index.php,v 1.3 2009/04/16 14:22:22 redmorris Exp $

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/lib/statslib.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once('lib.php');

    admin_externalpage_setup('reportcategoryactivity');
    admin_externalpage_print_header();

    $report     = optional_param('report', STATS_REPORT_ACTIVE_COURSES, PARAM_INT);
    $time       = optional_param('time', 0, PARAM_INT);
    $numcategories = optional_param('numcategories', 20, PARAM_INT);

    require_capability('moodle/site:viewreports', get_context_instance(CONTEXT_SYSTEM, SITEID));  // needed?

    if (empty($CFG->enablestats)) {
        redirect("$CFG->wwwroot/$CFG->admin/settings.php?section=stats", get_string('mustenablestats', 'admin'), 3);
    }


    $course = get_site();
    stats_check_uptodate($course->id);

    $strreports = get_string('reports');
    $strcourseoverview = get_string('category');

    $reportoptions = category_activity_stats_get_report_options($course->id,STATS_MODE_CATEGORIES);

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

    echo '<form action="index.php" method="post">'."\n";
    echo '<fieldset class="invisiblefieldset">';

    $table->width = '*';
    $table->align = array('left','left','left','left','left','left');
    $table->data[] = array(get_string('statsreporttype'),choose_from_menu($reportoptions,'report',$report,'','','',true),
                           get_string('statstimeperiod'),choose_from_menu($timeoptions,'time',$time,'','','',true),
                           '<input type="text" name="numcategories" size="3" maxlength="3" value="'.$numcategories.'" />',
                           '<input type="submit" value="'.get_string('view').'" />') ;

    print_table($table);
    echo '</fieldset>';
    echo '</form>';

    if (!empty($report) && !empty($time)) {
        $param = category_activity_stats_get_parameters($time,$report,SITEID,STATS_MODE_CATEGORIES);

        if (!empty($param->sql)) {
            $sql = $param->sql;
        } else {
            $sql = "SELECT courseid,".$param->fields.", ".$CFG->prefix."course.category, ".$CFG->prefix."course_categories.name"
				." FROM (".$CFG->prefix.'stats_'.$param->table." INNER JOIN ".$CFG->prefix."course"
				." ON ".$CFG->prefix."stats_".$param->table.".courseid = ".$CFG->prefix."course.id)"
				." INNER JOIN ".$CFG->prefix."course_categories ON ".$CFG->prefix."course_categories.id = ".$CFG->prefix."course.category"
                ." WHERE timeend >= ".$param->timeafter.' AND stattype = \'activity\''
                ." GROUP BY category "
                ." ORDER BY ".$param->orderby;
        }

        $courses = get_records_sql($sql, 0, $numcategories);

        if (empty($courses)) {
            notify(get_string('statsnodata'));echo '</td></tr></table>';echo '<p>after notify</p>';

        } else {
			print_heading(format_string(get_string('statsreport'.$report,'report_categoryactivity')));

            if (empty($CFG->gdversion)) {
                echo '<div class="boxaligncenter">(' . get_string("gdneed") .')</div>';
            } else {
                echo '<div class="boxaligncenter"><img alt="'.get_string('categoryoverviewgraph','report_categoryactivity').'" src="'.$CFG->wwwroot.'/'.$CFG->admin.'/report/categoryactivity/reportsgraph.php?time='.$time.'&report='.$report.'&numcategories='.$numcategories.'" /></div>';
            }

            $table = new StdClass;
            $table->align = array('left','center','center','center');
            $table->head = array(get_string('category'),$param->line1);
            if (!empty($param->line2)) {
                $table->head[] = $param->line2;
            }
            if (!empty($param->line3)) {
                $table->head[] = $param->line3;
            }

            foreach  ($courses as $c) {
                $a = array();
                $a[] = '<a href="'.$CFG->wwwroot.'/course/category.php?id='.$c->categoryid.'">'.$c->name.'</a>';

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

?>
