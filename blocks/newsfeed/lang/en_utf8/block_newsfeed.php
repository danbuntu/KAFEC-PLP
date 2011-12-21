<?php
/**
 * Language strings for news feed (the block and other components).
 *
 * @copyright &copy; 2006 The Open University
 * @author s.marshall@open.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package newsfeed
 *//** */

$string['backtonewsfeed'] = 'Back to feed: ';
$string['blockname'] = 'News feed';
$string['delete_error']='Database error encountered while attempting to delete newsfeed block';
$string['delete_forbidden']=
'It is not possible to delete this newsfeed block as it is included within other newsfeed blocks.
Press continue to view the list of the other newsfeed blocks involved.
';
$string['newsfeed'] = 'News feed';

$string['feedlist'] = 'News feeds';

$string['iconexpandfolder'] = 'Expand folder:';
$string['iconcollapsefolder'] = 'Collapse folder:';
$string['iconfeed'] = 'News feed:';

$string['includes'] = 'Includes';
$string['settings'] = 'Settings';
$string['editincludes'] = 'Select included feeds';
$string['includinglist'] = 'Included within';
$string['noincluding'] = 'This feed is not included within any others.';
$string['includeslist'] = 'Includes messages from';
$string['noincludes'] = 'This feed includes only its own messages.';
$string['availablelist'] = 'Available feeds';

$string['relativedate'] = '_ days since feed start';

$string['approvecount']='{$a->count} message{$a->plural} {$a->pluralhas} not yet been approved.';
$string['approveexplanation']='Students will not see the current version of such
messages (shown here with <span class=\'nf_unapprovedexample\'>this background colour</span>) until they are approved.';
$string['youcannotapprove']='You cannot yourself approve $a these messages because they come from other
feeds.';
$string['youcannotapprove_any']='';
$string['youcannotapprove_some']='some of';
$string['futureexplanation']='Messages that will only become visible to students at a future date appear <span class=\'nf_faintexample\'>in grey text</span>.';

$string['undelete']='Undelete';

$string['viewfeed']='View feed';

$string['viewall'] = 'View all messages in full';

$string['entryupdated']='Updated <em>{$a->timedisplay}</em> by <em>{$a->realname}</em> (<em>{$a->username}</em>)';
$string['entryapproved']='Approved <em>{$a->timedisplay}</em> by <em>{$a->realname}</em> (<em>{$a->username}</em>)';

$string['approve']='Approve';
$string['history']='History';
$string['post']='Add new message';
$string['paste']='Paste as new';

// Block settings
$string['field_showcount'] = 'Messages to show';
$string['field_showsummaries'] = 'Show descriptions';


// Edit feed page
$string['editfeedsettings']='Edit feed settings';

$string['field_name'] = 'Name';
$string['field_summary'] = 'Summary';
$string['field_type'] = 'Type';
$string['field_url'] = 'Address';
$string['field_checkfreq'] = 'Check for updates';
$string['field_public'] = 'Access';
$string['field_defaultauthid'] = 'Default authid';
$string['field_optionalauthid'] = 'Optional authid';

$string['type_internal']='In-house';
$string['type_external']='External Atom or RSS feed';

$string['header_internal']='In-house feed settings';
$string['header_external']='External feed settings';

$string['checkfreq_day']='Daily';
$string['checkfreq_manual']='Manually';

$string['access_public']='Available to all through Atom';
$string['access_private']='Restricted to display on site';

$string['authidrestricted']='Restricted to authids: <strong>$a</strong>';
$string['error_authid']='Authids can contain only capital letters and numbers';

// Edit entry page
$string['addnewmessage'] = 'Add new message';
$string['editmessage'] = 'Edit message';

$string['field_newsfeedid']='Feed';
$string['field_rollforward']='Type';
$string['field_appearancedate']='Date';
$string['field_title']='Title';
$string['field_text']='Text';
$string['field_attachment']='Attachment';

$string['header_authids']='Required authorisation';
$string['explanation_authids']='If all authid tokens are left unchecked then everyone sees the post. If any are selected then it appears only to people with those authid tokens.';

$string['rollforward_select']='Choose what happens to this message in future...';
$string['rollforward_relative']='Repeat in future presentations';
$string['rollforward_absolute']='Show only in this presentation';
$string['rollforward_required']='You must choose what happens to this message in future';

// View message/feed
$string['publishedandupdated']='{$a->published}<br />(Updated {$a->updated})';

$string['externalerror']='An error occurred at $a';

// Emails
$string['emailsendername']='News feed system';
$string['approvalrequestsubject']='$a - Approval request';
$string['approvalrequestmessage']=
'A change has been made to a message on $a->feedname. This change will not appear in public until you have{$a->another} visited the feed and clicked Approve against the new, deleted, or modified message.

You can visit the feed at the following URL:
<$a->url>

$a->recipients

{$a->otherconfirm}
Please do not reply to this automatically-generated message.
';
$string['approvalrequestoranother']=' (or another approver has)';
$string['approvalrequestrecipients']='Recipients of this email:';
$string['approvalrequestother']=
'If one of the other approvers confirms the changes, so that you need take no action, you will be informed by email.

';
$string['informapprovedsubject']='$a - Changes approved';
$string['informapprovedmessage']=
'The recent changes in $a->feedname have all now been approved; you may ignore the previous approval request.

Should you wish to view the feed, you can do so at the following URL:
<$a->url>


Please do not reply to this automatically-generated message.';

// Message history
$string['currentdraft']='Current draft';
$string['studentvisible']='Visible to students';
$string['makecurrent']='Make current';
$string['messagehistory']='Message history: $a';

// Newsfeed capabilities
$string['newsfeed:manage'] = 'Manage newsfeed';
$string['newsfeed:approve'] = 'Approve newsfeed';
$string['newsfeed:post'] = 'Post to newsfeed';

// Refactoring newsfeeds
$string['are_you_sure'] = 'Are you sure you want to move the newsfeed block to the course with shortname \'$a\'';
$string['block_not_found'] = 'Unable to move block - Newsfeed block does not exist';
$string['blockgeneral'] = 'General block settings';
$string['course_not_found'] = 'Unable to move block - Destination course does not exist';
$string['course_shortname'] = 'Destination course shortname';
$string['move_block'] = 'Move block';
$string['move_newsfeed_block'] = 'Move newsfeed block';
$string['nonewsfeedname'] = 'Required newsfeed name field is missing';
$string['shortname_not_found'] = 'Unable to move block - Course with shortname \'$a\' does not exist';

$string['feed_error'] = 'Error loading news feed.';
?>