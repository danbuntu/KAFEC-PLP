<?php // $Id: graph17.php,v 1.0 2009/03/09 11:44:00 moodler Exp $

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

    $param = comparative_reports_stats_get_parameters($time,$report,SITEID,STATS_MODE_RANKED);

    if (!empty($param->sql)) {
        $sql = $param->sql;
    } else {
        $sql = 'SELECT s1.Month AS name, s1.activity as line1, s2.activity As line2, CASE WHEN s1.MonthIndex < 9 THEN s1.MonthIndex+12 ELSE s1.MonthIndex END As MonthIndex'
                .' FROM (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1188604800) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1220227199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s1'
                .' LEFT OUTER JOIN (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1220227200) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1251763199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month from from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s2'
                .' ON s2.monthindex = s1.monthindex'
                .' UNION'
                .' SELECT s2.Month AS name, s1.activity as line1, s2.activity As line2, CASE WHEN s2.MonthIndex < 9 THEN s2.MonthIndex+12 ELSE s2.MonthIndex END As MonthIndex'
                .' FROM (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1188604800) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1220227199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s1'
                .' RIGHT OUTER JOIN (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1220227200) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1251763199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month from from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s2'
                .' ON s2.monthindex = s1.monthindex'
                .' ORDER BY MonthIndex';
    }

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
        $graph->x_data[] = $c->name;
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
