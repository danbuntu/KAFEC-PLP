<?php // $Id: graph20.php,v 1.0 2009/03/09 11:43:00 moodler Exp $

    require_once('../../../config.php');
    require_once($CFG->dirroot.'/lib/statslib.php');
    require_once($CFG->dirroot.'/lib/graphlib.php');
    require_once('lib.php');

    $report     = required_param('report', PARAM_INT);
    $time       = required_param('time', PARAM_INT);
    $numcourses = required_param('numcourses', PARAM_INT);
    $userid		= optional_param('userid', 0, PARAM_INT);

    require_login();

    require_capability('moodle/site:viewreports', get_context_instance(CONTEXT_SYSTEM));

    stats_check_uptodate();

    $param = comparative_reports_stats_get_parameters($time,$report,SITEID,STATS_MODE_RANKED);

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

    $courses = get_records_sql($sql, 0, $numcourses);

    $courses = stats_fix_zeros($courses,$param->timeafter,$param->table,(!empty($param->line2)),(!empty($param->line3)));
	$courses = array_reverse($courses);
	
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
        $graph->x_data[] = userdate($c->timeend,get_string('strftimedate'),$CFG->timezone);
        $graph->y_data['line1'][] = $c->line1;
        if (isset($c->line2)) {
            $graph->y_data['line2'][] = $c->line2;
        }
        if (isset($c->line3)) {
            $graph->y_data['line3'][] = $c->line3;
        }
    }
    $graph->y_order = array('line1');
    $graph->y_format['line1'] = array('colour' => 'blue','line' => 'line','legend' => $param->line1);
    if (!empty($param->line2)) {
        $graph->y_order[] = 'line2';
        $graph->y_format['line2'] = array('colour' => 'green','line' => 'line','legend' => $param->line2);
    }
    if (!empty($param->line3)) {
        $graph->y_order[] = 'line3';
        $graph->y_format['line3'] = array('colour' => 'red','line' => 'line','legend' => $param->line3);
    }

    $graph->draw_stack();

?>
