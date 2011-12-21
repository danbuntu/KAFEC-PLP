<?PHP

    require_once('../../../config.php');
    require_once($CFG->libdir.'/adminlib.php');

    $context = get_context_instance(CONTEXT_SYSTEM);
    require_capability('moodle/site:readallmessages', $context);

    admin_externalpage_setup('messagelog');
    admin_externalpage_print_header();

/*
    Original SQL by Ian Cannonier, here:
        http://moodle.org/mod/forum/discuss.php?d=27559

    Revision 1 by Will H
        enhanced the SQL to:
            look up usernames and last-logon times
            find unread and read messages
        rendered results in a table
        added the ability to filter by message age [bug: fixed at <10 days or forever]
        added a permission check for moodle:site/readallmessages

    Revision 2 by Mike Worth
        added the ability to filter by sender, recipient, both (and) or either (or)

    Revision 3 by Will H
        fixed a bug to allow proper filtering by message age
        added the ability to sort results in various ways
        tidied SQL generation code
        added a front end

    Revision 4 by Will H
        added a link to quickly find all messages involving a particular user
        added a link to quickly remove all filters and show all messages
        
    Revision 5 by Mike Worth
        changed hardcoded mysql function to general moodle get_records_sql function
        changed final display to use the returned objects and display actual names rather than usernames
*/



function lookup_user($username) {
    if(!$user = get_record('user','username',$username)){return false;}
    return $user->id;
}

// Qualify the query with user-specified conditions

$qual = array();
if      ( isset($_GET['days']) ) {
    $days               = $_GET['days'];
    if ( $days > 0 )    { $qual['days'] = "msg{\$read}.timecreated > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL - $days DAY))"; }
    else        { $days = ''; }
}
if ( isset($_GET['user']) ) {
    if ( $user = $_GET['user'] ) {
        $userid             = lookup_user($user);
        $qual['user']       = "( sender{\$read}.id = '$userid' OR recipient{\$read}.id = '$userid' )";
    }
    else { $user = ''; }
}
if (!isset($qual['user']) or ! $qual['user'] ) { 
    if ( isset($_GET['sender']) ) {
        if ( $sndr = $_GET['sender'] ) {
            $userid             = lookup_user($sndr);
            $qual['sndr']   = "sender{\$read}.id = '$userid'";
        }
        else { $sndr = ''; }
    }
    if ( isset($_GET['recipient']) ) {
        if ( $rcpt = $_GET['recipient'] ) {
            $userid             = lookup_user($rcpt);
            $qual['rcpt']   = "recipient{\$read}.id = '$userid'";
        }
        else { $rcpt = ''; }
    }
}

// Build the two query qualifier strings - one needed for read messages; one for unread

if ( ! $qual ) {
    $qualifierr = '';
    $qualifieru = '';
} else {
    $qualifiers = 'WHERE ' . implode(' AND ', $qual);
    // echo "<p>Qualifiers = $qualifiers</p>\n\n";  // for debugging
    $read       = 'r';
    eval("\$qualifierr  = \"$qualifiers\";");
    $read       = 'u';
    eval("\$qualifieru  = \"$qualifiers\";");
}

// Decode the user's choice of sort order

$sortorder              = 0;
$orderby                = '1 DESC';                 // Sent date (newest first); read/unread combined   newest (default)
if ( isset($_GET['sortorder'] ) ) { $sortorder = $_GET['sortorder']; }
switch ( $sortorder ) {
    case 1: $orderby    = '1 ASC'; break;           // Sent date (oldest first); read/unread combined   oldest
    case 2: $orderby    = '10 ASC, 5 DESC, 1 DESC'; break;  // Read first, then read date (newest first), then sent date (newest first)     most recently viewed
    case 3: $orderby    = '10 ASC, 5 ASC, 1 ASC';   break;  // Read first, read date (oldest first), then sent date (newest first)              least recently viewed
    case 4: $orderby    = '10 ASC, 1 DESC'; break;  // Read first, then sent date (newest first)        newest viewed
    case 5: $orderby    = '10 ASC, 1 ASC';  break;  // Read first, then sent date (oldest first);       oldest viewed
    case 6: $orderby    = '10 DESC, 1 DESC'; break; // Unread first, then sent date (newest first)      newest unviewed
    case 7: $orderby    = '10 DESC, 1 ASC'; break;  // Unread first, then sent date (oldest first)      oldest unviewed
}
$orderby                = 'ORDER BY ' . $orderby;

// Build the main query
// For some reason, including numbers (even quoted) in the SELECT clause, seems to break the result
// so we use 'r' and 'u' to allow us to sort by message status (read / unread)

$query          = <<< END_OF_SQL
SELECT FROM_UNIXTIME(timecreated) as timecreated, senderr.username as sender, message, recipientr.username as recipient, FROM_UNIXTIME(timeread) as timeread, senderr.id as senderid, recipientr.id as recipientid, FROM_UNIXTIME(senderr.lastaccess) as senderlastaccess, FROM_UNIXTIME(recipientr.lastaccess) as recipientlastaccess, 'r', concat(senderr.firstname,' ',senderr.lastname) as sendername, concat(recipientr.firstname,' ',recipientr.lastname) as recipientname
FROM {$CFG->prefix}message_read msgr
INNER JOIN {$CFG->prefix}user senderr ON msgr.useridfrom=senderr.id
INNER JOIN {$CFG->prefix}user recipientr ON msgr.useridto=recipientr.id
$qualifierr
UNION ALL
SELECT FROM_UNIXTIME(timecreated) as timecreated, senderu.username as sender, message, recipientu.username as recipient, 'unread', senderu.id as senderid, recipientu.id as recipientid, FROM_UNIXTIME(senderu.lastaccess) as senderlastaccess, FROM_UNIXTIME(recipientu.lastaccess) as recipientlastacess, 'u', concat(senderu.firstname,' ',senderu.lastname) as sendername, concat(recipientu.firstname,' ',recipientu.lastname) as recipientname
FROM {$CFG->prefix}message msgu
INNER JOIN {$CFG->prefix}user senderu ON msgu.useridfrom=senderu.id
INNER JOIN {$CFG->prefix}user recipientu ON msgu.useridto=recipientu.id
$qualifieru
$orderby;
END_OF_SQL;

// Display the front end

echo "\n\n\n<form action=\"{$_SERVER['PHP_SELF']}\" method=\"get\">\n";
echo "<table border=\"1\" cellpadding=\"10\" width = \"100%\"><tr>";

echo "  <td align=\"center\">Show <select name=\"sortorder\" onchange=\"submit()\">";
$options = array('newest', 'oldest', 'most recently viewed', 'least recently viewed', 'newest viewed', 'oldest viewed', 'newest unviewed', 'oldest unviewed');
foreach ( $options as $value => $label ) {
    echo "<option value=\"$value\"";
    if ( $sortorder == $value ) { echo " selected"; }
    echo ">$label</option>";
}
echo "</select><br>messages first</td>\n";

echo "  <td align=\"center\">";
if ( $qual )    { echo "<font color=\"#FF0000\">Showing only messages:</font><br><small>[<a href=\"{$_SERVER['PHP_SELF']}\">show all</a>]</small>"; }
else            { echo "Show only messages:"; }
echo "</td>\n";

echo "  <td align=\"center\">from the last<br><input size=\"2\" name=\"days\" value=\"";
if ( isset($qual['days']) ) { echo $days; }
echo "\"> days</td>\n";

echo "  <td align=\"center\">involving<br><input size=\"15\" name=\"user\" value=\"";
if ( isset($qual['user']) ) { echo $user; }
echo "\"></td>\n";

echo "  <td align=\"center\">from<br><input size=\"15\" name=\"sender\" value=\"";
if ( isset($qual['sndr']) ) { echo $sndr; }
echo "\"></td>\n";

echo "  <td align=\"center\">to<br><input size=\"15\" name=\"recipient\" value=\"";
if ( isset($qual['rcpt']) ) { echo $rcpt; }
echo "\"></td>\n";

echo "  <td align=\"center\"><input type=\"submit\" value=\"";
if ( $qual )    { echo "re-filter"; }
else            { echo "filter"; }
echo "\"></td>\n";

echo "</tr></table>\n";
echo "</form>\n\n";

echo "\n\n<hr>\n\n\n";

// Run the query
$result=get_records_sql($query);


// Display the results

if ( ! $result ) {
    if ( $qual )    { echo "<p>No messages match these criteria</p>\n\n"; }
    else            { echo "<p>No messages have been sent</p>\n\n"; }
} else {
    echo "<table border=\"1\" width = \"100%\">\n";
    echo "  <tr><th>Sent</th><th>From</th><th>To</th><th>Read</th><th>Message</th></tr>\n";
    foreach($result as $row){
        echo "  <tr>\n";
        echo "    <td align=\"center\">". substr($row->timecreated,0,10) .'<br>'. substr($row->timecreated,11,8) ."</td>\n";
        echo "    <td align=\"center\" nowrap><a href=\"{$CFG->wwwroot}/user/view.php?id={$row->senderid}\" title=\"last access: {$row->senderlastaccess}\">{$row->sendername}</a><br><small>[<a href=\"{$_SERVER['PHP_SELF']}?user={$row->sender}\" title=\"Show all messages involving {$row->sendername}\">more</a>]</small></td>\n";
        echo "    <td align=\"center\" nowrap><a href=\"{$CFG->wwwroot}/user/view.php?id={$row->recipientid}\" title=\"last access: {$row->recipientlastaccess}\">{$row->recipientname}</a><br><small>[<a href=\"{$_SERVER['PHP_SELF']}?user={$row->recipient}\" title=\"Show all messages involving {$row->recipientname}\">more</a>]</small></td>\n";
        echo "    <td align=\"center\">". substr($row->timeread,0,10) .'<br>'. substr($row->timeread,11,8) ."</td>\n";
        echo "    <td>". str_replace("\n","<br>\n", $row->message) ."</td>\n";
        echo "  </tr>\n";
    }
    echo "</table>\n";
}

admin_externalpage_print_footer();

?>
