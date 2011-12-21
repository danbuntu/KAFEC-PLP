The message log report is based on code originally written by Will H and Mike Worth, discussed at http://moodle.org/mod/forum/discuss.php?d=27559.

Filters include the order of the message, the number of days worth of messages to get, and the username to, from, or both.

Clicking on the [more] link in the to or from columns will filter the messages which involve that user.

PLEASE NOTE:
This report is compatible with Moodle 1.9, but you must add a line to core Moodle files to make it work with Moodle 1.9.1-1.9.3.

It works as expected with Moodle 1.9.4, simply copy the messagelog directory into the /admin/report directory of your install.

For earlier versions of Moodle 1.9, please see http://tracker.moodle.org/browse/CONTRIB-1185
Once you add the following line to /admin/settings/misc.php, the report will work as expected:

    $ADMIN->add('reports', new admin_externalpage('messagelog', get_string('messagelog', 'report_messagelog'), "$CFG->wwwroot/$CFG->admin/report/messagelog/index.php?days=30",'moodle/site:readallmessages')); 
