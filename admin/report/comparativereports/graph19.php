<?php // $Id: reportsgraph.php,v 1.8 2007/08/03 03:30:23 moodler Exp $

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/lib/statslib.php');
    require_once($CFG->dirroot.'/lib/graphlib.php');
    require_once('lib.php');

    $report     = required_param('report', PARAM_INT);
    $time       = required_param('time', PARAM_INT);
    $numcourses = required_param('numcourses', PARAM_INT);

    require_login();

    require_capability('moodle/site:viewreports', get_context_instance(CONTEXT_SYSTEM));

    stats_check_uptodate();

    $param = comparative_reports_stats_get_parameters($time,STATS_REPORT_USER_ALLACTIVITY,SITEID,STATS_MODE_RANKED);

    if (!empty($param->sql)) {
        $sql = $param->sql;
    } else {
        $sql = 'SELECT userid,sum(statsreads+statswrites) AS line1, '.sql_concat('firstname',"' '",'lastname').' AS name'
				.' FROM '.$CFG->prefix.'stats_user_'.$param->table.' INNER JOIN '.$CFG->prefix.'user'
				.' ON '.$CFG->prefix.'stats_user_'.$param->table.'.userid = '.$CFG->prefix.'user.id'
                .' WHERE userid > 1 AND timeend >= '.$param->timeafter.' AND stattype = \'activity\'' 
                .' GROUP BY userid'
                .' ORDER BY line1 DESC';
    }
	$param->graphline = 'line1';

    $courses = get_records_sql($sql, 0, $numcourses);

    if (empty($courses)) {
        error(get_string('statsnodata'),$CFG->wwwroot.'/'.$CFG->admin.'/report/course/index.php');
    }


    $graph = new graph(750,400);

    $graph->parameter['legend'] = 'outside-right';
    $graph->parameter['legend_size'] = 10;
    $graph->parameter['x_axis_angle'] = 90;
    $graph->parameter['title'] = false; // moodle will do a nicer job.
    $graph->y_tick_labels = null;
    $graph->offset_relation = null;
	
    foreach ($courses as $c) {
		// table, field to retrieve, field to compare, value to compare
        $graph->x_data[] = $c->name;
        $graph->y_data['bar1'][] = $c->{$param->graphline};
    }
    $graph->y_order = array('bar1');
    $graph->y_format['bar1'] = array('colour' => 'blue','bar' => 'fill','legend' => $param->{$param->graphline});

    $graph->draw_stack();

?>
