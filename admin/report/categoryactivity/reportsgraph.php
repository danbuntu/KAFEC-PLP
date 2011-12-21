<?php // $Id: reportsgraph.php,v 1.2 2009/03/24 16:42:23 redmorris Exp $

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/lib/statslib.php');
    require_once($CFG->dirroot.'/lib/graphlib.php');
    require_once('lib.php');

    $report     = required_param('report', PARAM_INT);
    $time       = required_param('time', PARAM_INT);
    $numcategories = required_param('numcategories', PARAM_INT);

    require_login();

    require_capability('moodle/site:viewreports', get_context_instance(CONTEXT_SYSTEM));

    stats_check_uptodate();

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
        error(get_string('statsnodata'),$CFG->wwwroot.'/'.$CFG->admin.'/report/categoryactivity/index.php');
    }


    $graph = new graph(750,400);

    $graph->parameter['legend'] = 'outside-right';
    $graph->parameter['legend_size'] = 10;
    $graph->parameter['x_axis_angle'] = 90;
    $graph->parameter['title'] = false; // moodle will do a nicer job.
    $graph->y_tick_labels = null;
    $graph->offset_relation = null;
    if ($report != STATS_REPORT_ACTIVE_COURSES) {
        $graph->parameter['y_decimal_left'] = 2;
    }

    foreach ($courses as $c) {
		// table, field to retrieve, field to compare, value to compare
        $graph->x_data[] = get_field('course_categories','name','path',$c->path);
        $graph->y_data['bar1'][] = $c->{$param->graphline};
    }
    $graph->y_order = array('bar1');
    $graph->y_format['bar1'] = array('colour' => 'blue','bar' => 'fill','legend' => $param->{$param->graphline});

    $graph->draw_stack();

?>
