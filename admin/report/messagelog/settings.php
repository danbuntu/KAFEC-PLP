<?php  // $Id: settings.php,v 1.2 2009/03/30 16:28:06 mcampbell Exp $
$ADMIN->add('reports', new admin_externalpage('messagelog', get_string('messagelog', 'report_messagelog'), "$CFG->wwwroot/$CFG->admin/report/messagelog/index.php?days=30",'moodle/site:readallmessages'));
?>