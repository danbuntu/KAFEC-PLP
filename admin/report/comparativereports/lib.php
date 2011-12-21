<?php  //$Id: lib.php,v 1.0 2009/04/15 10:51:32 redmorris Exp $

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com     //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

    // Comparative reports
    define('STATS_REPORT_YEAR_ON_YEAR',17);
    define('STATS_REPORT_ACTIVITY_BY_HOUR',18);
    define('STATS_REPORT_ACTIVE_USERS',19);
    define('STATS_REPORT_ONE_USERS_ACTIVITY',20);
    define('STATS_REPORT_COURSE_DISK_USAGE',21);

    define('STATS_MODE_COMPARATIVE',5);

    function comparative_reports_stats_get_parameters($time,$report,$courseid,$mode,$roleid=0) {
    global $CFG,$db;

    $param = new object();

    if ($time < 10) { // dailies
        // number of days to go back = 7* time
        $param->table = 'daily';
        $param->timeafter = strtotime("-".($time*7)." days",stats_get_base_daily());
    } elseif ($time < 20) { // weeklies
        // number of weeks to go back = time - 10 * 4 (weeks) + base week
        $param->table = 'weekly';
        $param->timeafter = strtotime("-".(($time - 10)*4)." weeks",stats_get_base_weekly());
    } else { // monthlies.
        // number of months to go back = time - 20 * months + base month
        $param->table = 'monthly';
        $param->timeafter = strtotime("-".($time - 20)." months",stats_get_base_monthly());
    }

    $param->extras = '';

    // compatibility - if we're in postgres, cast to real for some reports.
    $real = '';
    if ($CFG->dbfamily == 'postgres') {
        $real = '::real';
    }

    switch ($report) {
    // ******************** STATS_MODE_GENERAL ******************** //
    case STATS_REPORT_LOGINS:
        $param->fields = 'timeend,sum(stat1) as line1,sum(stat2) as line2';
        $param->fieldscomplete = true;
        $param->stattype = 'logins';
        $param->line1 = get_string('statslogins');
        $param->line2 = get_string('statsuniquelogins');
        if ($courseid == SITEID) {
            $param->extras = 'GROUP BY timeend';
        }
        break;

    case STATS_REPORT_READS:
        $param->fields = sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, stat1 as line1';
        $param->fieldscomplete = true; // set this to true to avoid anything adding stuff to the list and breaking complex queries.
        $param->aggregategroupby = 'roleid';
        $param->stattype = 'activity';
        $param->crosstab = true;
        $param->extras = 'GROUP BY timeend,roleid,stat1';
        if ($courseid == SITEID) {
            $param->fields = sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, sum(stat1) as line1';
            $param->extras = 'GROUP BY timeend,roleid';
        }
        break;

    case STATS_REPORT_WRITES:
        $param->fields = sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, stat2 as line1';
        $param->fieldscomplete = true; // set this to true to avoid anything adding stuff to the list and breaking complex queries.
        $param->aggregategroupby = 'roleid';
        $param->stattype = 'activity';
        $param->crosstab = true;
        $param->extras = 'GROUP BY timeend,roleid,stat2';
        if ($courseid == SITEID) {
            $param->fields = sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, sum(stat2) as line1';
            $param->extras = 'GROUP BY timeend,roleid';
        }
        break;

    case STATS_REPORT_ACTIVITY:
        $param->fields = sql_concat('timeend','roleid').' AS uniqueid, timeend, roleid, sum(stat1+stat2) as line1';
        $param->fieldscomplete = true; // set this to true to avoid anything adding stuff to the list and breaking complex queries.
        $param->aggregategroupby = 'roleid';
        $param->stattype = 'activity';
        $param->crosstab = true;
        $param->extras = 'GROUP BY timeend,roleid';
        if ($courseid == SITEID) {
            $param->extras = 'GROUP BY timeend,roleid';
        }
        break;

    case STATS_REPORT_ACTIVITYBYROLE;
        $param->fields = 'stat1 AS line1, stat2 AS line2';
        $param->stattype = 'activity';
        $rolename = get_field('role','name','id',$roleid);
        $param->line1 = $rolename . get_string('statsreads');
        $param->line2 = $rolename . get_string('statswrites');
        if ($courseid == SITEID) {
            $param->extras = 'GROUP BY timeend';
        }
        break;

    // ******************** STATS_MODE_DETAILED ******************** //
    case STATS_REPORT_USER_ACTIVITY:
        $param->fields = 'statsreads as line1, statswrites as line2';
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->stattype = 'activity';
        break;

    case STATS_REPORT_USER_ALLACTIVITY:
        $param->fields = 'statsreads+statswrites as line1';
        $param->line1 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;

    case STATS_REPORT_USER_LOGINS:
        $param->fields = 'statsreads as line1';
        $param->line1 = get_string('statsuserlogins');
        $param->stattype = 'logins';
        break;

    case STATS_REPORT_USER_VIEW:
        $param->fields = 'statsreads as line1, statswrites as line2, statsreads+statswrites as line3';
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->line3 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;

    // ******************** STATS_MODE_RANKED ******************** //
    case STATS_REPORT_ACTIVE_COURSES:
        $param->fields = 'sum(stat1+stat2) AS line1';
        $param->stattype = 'activity';
        $param->orderby = 'line1 DESC';
        $param->line1 = get_string('activity');
        $param->graphline = 'line1';
        break;

    case STATS_REPORT_ACTIVE_COURSES_WEIGHTED:
        $threshold = 0;
        if (!empty($CFG->statsuserthreshold) && is_numeric($CFG->statsuserthreshold)) {
            $threshold = $CFG->statsuserthreshold;
        }
        $param->fields = '';
        $param->sql = 'SELECT activity.courseid, activity.all_activity AS line1, enrolments.highest_enrolments AS line2,
                        activity.all_activity / enrolments.highest_enrolments as line3
                       FROM (
                            SELECT courseid, (stat1+stat2) AS all_activity
                              FROM '.$CFG->prefix.'stats_'.$param->table.'
                             WHERE stattype=\'activity\' AND timeend >= '.$param->timeafter.' AND roleid = 0
                       ) activity
                       INNER JOIN
                            (
                            SELECT courseid, max(stat1) AS highest_enrolments 
                              FROM '.$CFG->prefix.'stats_'.$param->table.'
                             WHERE stattype=\'enrolments\' AND timeend >= '.$param->timeafter.' AND stat1 > '.$threshold.' 
                          GROUP BY courseid
                      ) enrolments
                      ON (activity.courseid = enrolments.courseid)
                      ORDER BY line3 DESC';
        $param->line1 = get_string('activity');
        $param->line2 = get_string('users');
        $param->line3 = get_string('activityweighted');
        $param->graphline = 'line3';
        break;

    case STATS_REPORT_PARTICIPATORY_COURSES:
        $threshold = 0;
        if (!empty($CFG->statsuserthreshold) && is_numeric($CFG->statsuserthreshold)) {
            $threshold = $CFG->statsuserthreshold;
        }
        $param->fields = '';
        $param->sql = 'SELECT courseid, ' . sql_ceil('avg(all_enrolments)') . ' as line1, ' .
                         sql_ceil('avg(active_enrolments)') . ' as line2, avg(proportion_active) AS line3
                       FROM (
                           SELECT courseid, timeend, stat2 as active_enrolments,
                                  stat1 as all_enrolments, stat2'.$real.'/stat1'.$real.' as proportion_active
                             FROM '.$CFG->prefix.'stats_'.$param->table.'
                            WHERE stattype=\'enrolments\' AND roleid = 0 AND stat1 > '.$threshold.'
                       ) aq
                       WHERE timeend >= '.$param->timeafter.'
                       GROUP BY courseid
                       ORDER BY line3 DESC';

        $param->line1 = get_string('users');
        $param->line2 = get_string('activeusers');
        $param->line3 = get_string('participationratio');
        $param->graphline = 'line3';
        break;

    case STATS_REPORT_PARTICIPATORY_COURSES_RW:
        $param->fields = '';
        $param->sql =  'SELECT courseid, sum(views) AS line1, sum(posts) AS line2,
                           avg(proportion_active) AS line3
                         FROM (
                           SELECT courseid, timeend, stat1 as views, stat2 AS posts,
                                  stat2'.$real.'/stat1'.$real.' as proportion_active
                             FROM '.$CFG->prefix.'stats_'.$param->table.'
                            WHERE stattype=\'activity\' AND roleid = 0 AND stat1 > 0
                       ) aq
                       WHERE timeend >= '.$param->timeafter.'
                       GROUP BY courseid
                       ORDER BY line3 DESC';
        $param->line1 = get_string('views');
        $param->line2 = get_string('posts');
        $param->line3 = get_string('participationratio');
        $param->graphline = 'line3';
        break;
        
    case STATS_REPORT_YEAR_ON_YEAR;
		$param->sql = 'SELECT s1.Month AS name, s1.activity as line1, s2.activity As line2, CASE WHEN s1.MonthIndex < 9 THEN s1.MonthIndex+12 ELSE s1.MonthIndex END As MonthIndex'
                .' FROM (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1188604800) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1220227199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s1'
                .' LEFT OUTER JOIN (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1220227200) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1251763199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month from from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s2'
                .' ON s2.monthindex = s1.monthindex'
                .' UNION'
                .' SELECT s2.Month AS name, s1.activity as line1, s2.activity As line2, CASE WHEN s2.MonthIndex < 9 THEN s2.MonthIndex+12 ELSE s2.MonthIndex END As MonthIndex'
                .' FROM (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1188604800) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1220227199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s1'
                .' RIGHT OUTER JOIN (SELECT '.$CFG->prefix.'stats_monthly.timeend AS timeend,from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)) AS Date,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%M\') AS Month,extract(month FROM from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))) AS MonthIndex,date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\') AS Year,sum(('.$CFG->prefix.'stats_monthly.stat1 + '.$CFG->prefix.'stats_monthly.stat2)) AS Activity FROM '.$CFG->prefix.'stats_monthly WHERE (('.$CFG->prefix.'stats_monthly.timeend >= 1220227200) AND ('.$CFG->prefix.'stats_monthly.timeend <= 1251763199)) GROUP BY '.$CFG->prefix.'stats_monthly.timeend ORDER BY extract(month from from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1))),date_format(from_unixtime(('.$CFG->prefix.'stats_monthly.timeend - 1)),_utf8\'%Y\')) s2'
                .' ON s2.monthindex = s1.monthindex'
                .' ORDER BY MonthIndex';
        $param->line1 = '07/08 '.get_string('activity');
        $param->line2 = '08/09 '.get_string('activity');
        $param->heading1 = 'Month';
        $param->graphline = 'line1';
        break;
    case STATS_REPORT_ACTIVITY_BY_HOUR;
		$param->sql = 'SELECT Count(\'x\') AS line1, DATE_FORMAT(FROM_UNIXTIME(time),\'%H:00 to %H:59\') AS name'
				.' FROM '.$CFG->prefix.'log'
				.' WHERE time >= '.$param->timeafter
				.' GROUP BY name';
        $param->line1 = get_string('activity');
        $param->heading1 = 'Hour';
        $param->graphline = 'line1';
		break;
	case STATS_REPORT_ACTIVE_USERS;
		$param->sql = 'SELECT userid,sum(statsreads+statswrites) AS line1, '.sql_concat('firstname',"' '",'lastname').' AS name'
				.' FROM '.$CFG->prefix.'stats_user_'.$param->table.' INNER JOIN '.$CFG->prefix.'user'
				.' ON '.$CFG->prefix.'stats_user_'.$param->table.'.userid = '.$CFG->prefix.'user.id'
                .' WHERE userid > 1 AND timeend >= '.$param->timeafter.' AND stattype = \'activity\''
                .' GROUP BY userid'
                .' ORDER BY line1 DESC';
        $param->line1 = get_string('activity');
        $param->heading1 = get_string('user');
        $param->tableurl = $CFG->wwwroot.'/user/view.php';
        $param->tableidfield = 'userid';
        $param->graphline = 'line1';
		break;
	case STATS_REPORT_ONE_USERS_ACTIVITY;
        $param->fields = 'sum(statsreads) as line1, sum(statswrites) as line2, sum(statsreads)+sum(statswrites) as line3, FROM_UNIXTIME(timeend) AS name';
        $param->extras = 'GROUP BY timeend';
        $param->orderby = 'timeend ASC';
        $period = str_replace('user_','',$param->table);
        switch ($period) {
            case 'daily'  : $period = get_string('day'); break;
            case 'weekly' : $period = get_string('week'); break;
            case 'monthly': $period = get_string('month', 'form'); break;
            default : $period = '';
        }
        $param->heading1 = get_string('periodending','moodle',$period);
        $param->line1 = get_string('statsuserreads');
        $param->line2 = get_string('statsuserwrites');
        $param->line3 = get_string('statsuseractivity');
        $param->stattype = 'activity';
        break;
    case STATS_REPORT_COURSE_DISK_USAGE;
        $param->sql = 'SELECT course.id, course.fullname AS name, category.name AS line1, \'\' AS line2'
                .' FROM '.$CFG->prefix.'course course, '.$CFG->prefix.'course_categories category'
                .' WHERE course.category = category.id'
                .' ORDER BY id';
        $param->heading1 = get_string('diskusageheading','report_comparativereports');
        $param->tableurl = $CFG->wwwroot.'/course/view.php';
        $param->tableidfield = 'id';
        $param->line1 = 'Category';
        $param->line2 = get_string('size');
        $param->nograph = true;
        break;
    }
    return $param;
}

function comparative_reports_stats_get_report_options($courseid,$mode) {
    global $CFG;

    $reportoptions = array();

    switch ($mode) {
    case STATS_MODE_GENERAL:
        $reportoptions[STATS_REPORT_ACTIVITY] = get_string('statsreport'.STATS_REPORT_ACTIVITY);
        if ($courseid != SITEID && $context == get_context_instance(CONTEXT_COURSE, $courseid)) {
            $sql = 'SELECT r.id,r.name FROM '.$CFG->prefix.'role r JOIN '.$CFG->prefix.'stats_daily s ON s.roleid = r.id WHERE s.courseid = '.$courseid;
            if ($roles == get_records_sql($sql)) {
                foreach ($roles as $role) {
                    $reportoptions[STATS_REPORT_ACTIVITYBYROLE.$role->id] = get_string('statsreport'.STATS_REPORT_ACTIVITYBYROLE). ' '.$role->name;
                }
            }
        }
        $reportoptions[STATS_REPORT_READS] = get_string('statsreport'.STATS_REPORT_READS);
        $reportoptions[STATS_REPORT_WRITES] = get_string('statsreport'.STATS_REPORT_WRITES);
        if ($courseid == SITEID) {
            $reportoptions[STATS_REPORT_LOGINS] = get_string('statsreport'.STATS_REPORT_LOGINS);
        }

        break;
    case STATS_MODE_DETAILED:
        $reportoptions[STATS_REPORT_USER_ACTIVITY] = get_string('statsreport'.STATS_REPORT_USER_ACTIVITY);
        $reportoptions[STATS_REPORT_USER_ALLACTIVITY] = get_string('statsreport'.STATS_REPORT_USER_ALLACTIVITY);
        if (has_capability('coursereport/stats:view', get_context_instance(CONTEXT_SYSTEM))) {
            $site = get_site();
            $reportoptions[STATS_REPORT_USER_LOGINS] = get_string('statsreport'.STATS_REPORT_USER_LOGINS);
        }
        break;
    case STATS_MODE_RANKED:
        if (has_capability('coursereport/stats:view', get_context_instance(CONTEXT_SYSTEM))) {
            $reportoptions[STATS_REPORT_ACTIVE_COURSES] = get_string('statsreport'.STATS_REPORT_ACTIVE_COURSES);
            $reportoptions[STATS_REPORT_ACTIVE_COURSES_WEIGHTED] = get_string('statsreport'.STATS_REPORT_ACTIVE_COURSES_WEIGHTED);
            $reportoptions[STATS_REPORT_PARTICIPATORY_COURSES] = get_string('statsreport'.STATS_REPORT_PARTICIPATORY_COURSES);
            $reportoptions[STATS_REPORT_PARTICIPATORY_COURSES_RW] = get_string('statsreport'.STATS_REPORT_PARTICIPATORY_COURSES_RW);
        }
        break;
	case STATS_MODE_COMPARATIVE:
        $reportoptions[STATS_REPORT_YEAR_ON_YEAR] = get_string('statsreport'.STATS_REPORT_YEAR_ON_YEAR,'report_comparativereports');
        $reportoptions[STATS_REPORT_ACTIVITY_BY_HOUR] = get_string('statsreport'.STATS_REPORT_ACTIVITY_BY_HOUR,'report_comparativereports');
        $reportoptions[STATS_REPORT_ACTIVE_USERS] = get_string('statsreport'.STATS_REPORT_ACTIVE_USERS,'report_comparativereports');
        $reportoptions[STATS_REPORT_ONE_USERS_ACTIVITY] = get_string('statsreport'.STATS_REPORT_ONE_USERS_ACTIVITY,'report_comparativereports');
        $reportoptions[STATS_REPORT_COURSE_DISK_USAGE] = get_string('statsreport'.STATS_REPORT_COURSE_DISK_USAGE,'report_comparativereports');
		break;
    }

    return $reportoptions;
}    
