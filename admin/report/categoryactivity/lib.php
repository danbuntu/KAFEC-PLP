<?php  //$Id: lib.php,v 1.2 2009/04/16 14:22:22 redmorris Exp $

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

    // Category reports
    define('STATS_REPORT_ACTIVE_CATEGORIES_TOP',15); // course activity within all the top categories children
    define('STATS_REPORT_ACTIVE_CATEGORIES',16); // course activity within all the categories
    define('STATS_MODE_CATEGORIES',4);
    
    function category_activity_stats_get_parameters($time,$report,$courseid,$mode,$roleid=0) {
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
        case STATS_REPORT_ACTIVE_CATEGORIES_TOP;
            $param->sql = 'SELECT cats.path, REPLACE(substring(cats.path, 2, 2),\'/\',\'\') AS categoryid, cats.name, SUM(COALESCE(mergedstats.line1,0)) AS line1'
                .' FROM '.$CFG->prefix.'course_categories cats LEFT JOIN'
                .' (SELECT course.category, sum(stat1+stat2) AS line1'
                .' FROM '.$CFG->prefix.'stats_'.$param->table.' stats'
                .' JOIN '.$CFG->prefix.'course course ON course.id = stats.courseid'
                .' WHERE stats.timeend >= '.$param->timeafter.' AND stats.stattype = \'activity\''
                .' GROUP BY category) mergedstats'
                .' ON cats.id = mergedstats.category'
                .' GROUP BY categoryid'
                .' ORDER BY line1 DESC, name ASC';
            $param->line1 = get_string('activity');
            $param->graphline = 'line1';
            break;
        case STATS_REPORT_ACTIVE_CATEGORIES;
            $param->sql = 'SELECT cats.path, REPLACE(substring(cats.path, 2, 2),\'/\',\'\') AS categoryid, cats.name, COALESCE(mergedstats.line1,0) AS line1'
                .' FROM '.$CFG->prefix.'course_categories cats LEFT JOIN'
                .' (SELECT course.category, sum(stat1+stat2) AS line1'
                .' FROM '.$CFG->prefix.'stats_'.$param->table.' stats'
                .' JOIN '.$CFG->prefix.'course course ON course.id = stats.courseid'
                .' WHERE stats.timeend >= '.$param->timeafter.' AND stats.stattype = \'activity\''
                .' GROUP BY category) mergedstats'
                .' ON cats.id = mergedstats.category'
                .' ORDER BY line1 DESC, name ASC';
            $param->line1 = get_string('activity');
            $param->graphline = 'line1';
            break;
    }
    return $param;
}

function category_activity_stats_get_report_options($courseid,$mode) {
    global $CFG;

    $reportoptions = array();

    switch ($mode) {
    case STATS_MODE_GENERAL:
        $reportoptions[STATS_REPORT_ACTIVITY] = get_string('statsreport'.STATS_REPORT_ACTIVITY);
        if ($courseid !== SITEID && $context == get_context_instance(CONTEXT_COURSE, $courseid)) {
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
    case STATS_MODE_CATEGORIES:
        $reportoptions[STATS_REPORT_ACTIVE_CATEGORIES_TOP] = get_string('statsreport'.STATS_REPORT_ACTIVE_CATEGORIES_TOP,'report_categoryactivity');
        $reportoptions[STATS_REPORT_ACTIVE_CATEGORIES] = get_string('statsreport'.STATS_REPORT_ACTIVE_CATEGORIES,'report_categoryactivity');
    break;
    }

    return $reportoptions;
}    
