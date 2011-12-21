<?php

    /**
     * SQL library file for MoodleTxt
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011021101
     * @since 2007022212
    */

    $p = $CFG->prefix;

    $mdltxt_sql = array();

    // MySQL
    $mdltxt_sql['mysql']['addressmovecontacts'] = '
        UPDATE ' . $p . 'block_mtxt_ab_entry
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['mysql']['addressmovegroups'] = '
        UPDATE ' . $p . 'block_mtxt_ab_groups
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['mysql']['admingetaccandinbox'] = '
        SELECT acc.*, inbox.userid
        FROM ' . $p . 'block_mtxt_accounts AS acc
        LEFT JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON acc.defaultinbox = inbox.id
        ORDER BY acc.username ASC';

    $mdltxt_sql['mysql']['admingetaccountlist'] = '
        SELECT DISTINCT acc.*, COUNT(outbox.id) AS messagecount
        FROM ' . $p . 'block_mtxt_accounts AS acc
        LEFT JOIN ' . $p . 'block_mtxt_outbox AS outbox
        ON acc.id = outbox.txttoolsaccount
        GROUP BY acc.id';

    $mdltxt_sql['mysql']['admingetuserlinkfrag'] = '
        moodleuser = \'%1$d\'
        AND courseid = \'%2$d\'
        AND active = %3$d';

    $mdltxt_sql['mysql']['admingetusersonfilter'] = '
        SELECT usertable.id, usertable.username, usertable.firstname, usertable.lastname
        FROM ' . $p . 'user AS usertable
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON usertable.id = inbox.userid
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_filter AS filter
            ON filterlink.filter = filter.id
        WHERE filter.id = %1$d
        ORDER BY usertable.lastname ASC, usertable.firstname ASC';

    $mdltxt_sql['mysql']['admingetrssrecord'] = '
        SELECT * FROM ' . $p . 'block_mtxt_rss
        WHERE expirytime > %1$d
        ORDER BY pubtime DESC
        LIMIT 1';

    $mdltxt_sql['mysql']['adminsearchusers'] = '
        SELECT id, firstname, lastname, username
        FROM ' . $p . 'user
        WHERE deleted = 0
        AND (firstname LIKE \'%%%1$s%%\'
        OR lastname LIKE \'%%%1$s%%\'
        OR username LIKE \'%%%1$s%%\')
        ORDER BY lastname ASC, firstname ASC
        LIMIT 15';

    $mdltxt_sql['mysql']['crondeadaddressbooklinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_ab_users WHERE id IN (
            SELECT id FROM (
                SELECT linktable.id
                FROM ' . $p . 'block_mtxt_ab_users AS linktable
                LEFT JOIN ' . $p . 'user AS usertable
                    ON linktable.userid = usertable.id
                WHERE usertable.id IS NULL
            ) AS blahblah
        )';

    $mdltxt_sql['mysql']['crondeadinboxlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_in_user WHERE id IN (
            SELECT id FROM (
                SELECT linktable.id
                FROM ' . $p . 'block_mtxt_in_user AS linktable
                LEFT JOIN ' . $p . 'user AS usertable
                    ON linktable.userid = usertable.id
                WHERE usertable.id IS NULL
            ) AS blahblah
        )';

    $mdltxt_sql['mysql']['crondeadsentlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_sent_user WHERE id IN (
            SELECT id FROM (
                SELECT linktable.id
                FROM ' . $p . 'block_mtxt_sent_user AS linktable
                LEFT JOIN ' . $p . 'user AS usertable
                    ON linktable.userid = usertable.id
                WHERE usertable.id IS NULL
            ) AS blahblah
        )';

    $mdltxt_sql['mysql']['crongetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent as sent
        INNER JOIN ' . $p . 'block_mtxt_outbox AS messages
            ON sent.messageid = messages.id
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE messages.txttoolsaccount = %1$d
        AND status.status = %2$d OR status.status = %3$d';

    $mdltxt_sql['mysql']['crongetsentfrag'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['mysql']['groupsgetlinkedcontacts'] = '
        SELECT contacts.id FROM ' . $p . 'block_mtxt_ab_entry AS contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem AS link
            ON contacts.id = link.contact
        WHERE link.groupid = %1$d';

    $mdltxt_sql['mysql']['groupsgetmembers'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_grpmem
        WHERE groupid IN %1$s';

    $mdltxt_sql['mysql']['groupsmergelinks'] = '
        UPDATE ' . $p . 'block_mtxt_ab_grpmem
        SET groupid = %1$d
        WHERE groupid = %2$d';

    $mdltxt_sql['mysql']['inboxgetdefaultinbox'] = '
        SELECT folder.id
        FROM ' . $p . 'block_mtxt_in_folders AS folder
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_accounts AS acc
            ON acc.defaultinbox = inbox.id
        WHERE folder.name = \'Inbox\'
        AND acc.id = %1$d';

    $mdltxt_sql['mysql']['inboxgetlinkedaccounts'] = '
        SELECT acc.*
        FROM ' . $p . 'block_mtxt_accounts AS acc
        INNER JOIN ' . $p . 'block_mtxt_filter AS filter
            ON acc.id = filter.account
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON filterlink.inbox = inbox.id
        WHERE inbox.id = %1$d
        AND acc.inboundenabled = 1';

    $mdltxt_sql['mysql']['inboxgetinboxfilters'] = '
        SELECT filterlink.id, filter.type, filter.value, folder.id AS folderid
        FROM ' . $p . 'block_mtxt_filter AS filter
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_in_folders AS folder
            ON folder.inbox = inbox.id
        WHERE filter.account = %1$d
        AND folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY filter.id ASC';

    $mdltxt_sql['mysql']['inboxgetinboxlist'] = '
        SELECT folder.id, user.firstname, user.lastname, user.username
        FROM ' . $p . 'block_mtxt_in_folders AS folder
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'user AS user
            ON inbox.userid = user.id
        WHERE folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY user.lastname ASC';

    $mdltxt_sql['mysql']['inboxgetmessages'] = '
        SELECT messages.*, contacts.id AS contactid, contacts.firstname AS contactfirst, contacts.lastname AS contactlast, contacts.company AS contactcompany,
            users.id AS userid, users.firstname AS userfirst, users.lastname AS userlast, users.username
        FROM ' . $p . 'block_mtxt_in_mess AS messages
        LEFT JOIN ' . $p . 'block_mtxt_in_ab AS contactlink
            ON contactlink.receivedmessage = messages.id
        LEFT JOIN ' . $p . 'block_mtxt_ab_entry AS contacts
            ON contactlink.contact = contacts.id
        LEFT JOIN ' . $p . 'block_mtxt_in_user AS userlink
            ON userlink.receivedmessage = messages.id
        LEFT JOIN ' . $p . 'user AS users
            ON userlink.userid = users.id
        WHERE messages.folderid = %1$d
        ORDER BY %2$s
        LIMIT %3$d, %4$d';

    $mdltxt_sql['mysql']['inboxmarkfolderread'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET hasbeenread = 1
        WHERE folderid = %1$d';

    $mdltxt_sql['mysql']['inboxmovemessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE id IN (%2$s)';

    $mdltxt_sql['mysql']['inboxmoveallmessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE folderid = %2$d';

    $mdltxt_sql['mysql']['libgetcourseusers'] = '
        SELECT DISTINCT %1$s
        FROM ' . $p . 'user AS u
        INNER JOIN ' . $p . 'role_assignments AS r
            ON u.id  = r.userid
        WHERE r.contextid = %2$d';

    $mdltxt_sql['mysql']['sendgetabcontacts'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE addressbook IN (%1$s)';
        
    $mdltxt_sql['mysql']['sendgetabcontactsbyid'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE id IN (%1$s)';

    $mdltxt_sql['mysql']['sendgetabgroupmembers'] = '
        SELECT DISTINCT contacts.* FROM ' . $p . 'block_mtxt_ab_entry AS contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem AS link
            ON contacts.id = link.contact
        INNER JOIN ' . $p . 'block_mtxt_ab_groups AS groups
            ON link.groupid = groups.id
        WHERE groups.id IN (%1$s)';
        
    $mdltxt_sql['mysql']['sendgetabgroups'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_groups
        WHERE addressbook IN (%1$s)';
        
    $mdltxt_sql['mysql']['sendgetprivateabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab AS ab
        INNER JOIN  ' .$p . 'block_mtxt_ab_users AS link
            ON ab.id = link.addressbook
        INNER JOIN ' . $p . 'user AS usertable
            ON link.userid = usertable.id
        WHERE usertable.id = %1$d
        AND ab.type = \'private\'
        AND ab.owner <> %1$d';

    $mdltxt_sql['mysql']['sendgetpublicabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab AS ab
        WHERE ab.type = \'public\'
        OR ab.owner = %1$d';

    $mdltxt_sql['mysql']['sendgetuserdetails'] = '
        SELECT id, username, firstname, lastname, phone1, phone2
        FROM ' . $p . 'user
        WHERE id IN (%1$s)';

    $mdltxt_sql['mysql']['sentcountcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['mysql']['sentcountmessages'] = '
        SELECT COUNT(id)
        FROM ' . $p . 'block_mtxt_outbox AS o';

    $mdltxt_sql['mysql']['sentgetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE status.status = -1
        OR status.status = 2
        OR status.status = 5';

    $mdltxt_sql['mysql']['sentgetunfinishedmessages1'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent';

    $mdltxt_sql['mysql']['sentgetunfinishedmessages2'] = '
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['mysql']['sentselectcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['mysql']['sentselectmessages'] = '
        SELECT o.id, u.id AS moodleuserid, u.username AS moodleuser,
        acc.username AS txttoolsuser, o.messagetext, o.timesent
        FROM ' . $p . 'block_mtxt_outbox AS o
        INNER JOIN ' . $p . 'block_mtxt_accounts AS acc
            ON o.txttoolsaccount = acc.id
        INNER JOIN ' . $p . 'user AS u
            ON o.userid = u.id
        %1$s
        ORDER BY %2$s
        LIMIT %3$d
        OFFSET %4$d';

    $mdltxt_sql['mysql']['sentcountfrag'] = '
        SELECT COUNT(*)
        FROM ' . $p . 'block_mtxt_outbox
        WHERE useraccount IN (';

    $mdltxt_sql['mysql']['statsgetallusers'] = '
        SELECT u.id, u.username, u.firstname, u.lastname, SUM(s.numbersent) AS totalsent
        FROM ' . $p . 'block_mtxt_stats AS s
        INNER JOIN ' . $p . 'user AS u
            ON s.userid = u.id
        GROUP BY u.id
        ORDER BY totalsent DESC
        LIMIT %1$d
        OFFSET %2$d';

    $mdltxt_sql['mysql']['statsgetalldates'] = '
        SELECT date_entered, SUM(numbersent) AS totalsent
        FROM ' . $p . 'block_mtxt_stats
        GROUP BY date_entered
        ORDER BY date_entered DESC';

    $mdltxt_sql['mysql']['upgrade24sentmessages'] = '
        UPDATE ' . $p . 'block_mtxt_outbox
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['mysql']['upgrade24userstats'] = '
        UPDATE ' . $p . 'block_mtxt_stats
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['mysql']['viewgetmessagedetails'] = '
        SELECT o.*, u.id AS moodleuserid, u.username
        FROM ' . $p . 'block_mtxt_outbox AS o
        INNER JOIN ' . $p . 'user AS u
            ON o.userid = u.id
        WHERE o.id = %1$d';

    $mdltxt_sql['mysql']['viewgetrecipients'] = '
        SELECT sent.*, MAX(status.updatetime) AS latestupdate
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE sent.messageid = %1$d
        GROUP BY sent.id
        ORDER BY %2$s
        LIMIT %3$d
        OFFSET %4$d';
        
    $mdltxt_sql['mysql']['viewgetrecipientusers'] = '
        SELECT sent.id, user.id AS userid, user.username, user.firstname, user.lastname
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_sent_user AS link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'user AS user
            ON link.userid = user.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';

    $mdltxt_sql['mysql']['viewgetrecipientcontacts'] = '
        SELECT sent.id, contacts.id AS contactid, contacts.lastname, contacts.firstname, contacts.company
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_sent_ab AS link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'block_mtxt_ab_entry AS contacts
            ON link.contact = contacts.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';


    // POSTGRESQL
    $mdltxt_sql['postgres7']['addressmovecontacts'] = '
        UPDATE ' . $p . 'block_mtxt_ab_entry
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['postgres7']['addressmovegroups'] = '
        UPDATE ' . $p . 'block_mtxt_ab_groups
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['postgres7']['admingetaccandinbox'] = '
        SELECT acc.*, inbox.userid
        FROM ' . $p . 'block_mtxt_accounts AS acc
        LEFT JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON acc.defaultinbox = inbox.id
        ORDER BY acc.username ASC';

    $mdltxt_sql['postgres7']['admingetaccountlist'] = '
        SELECT DISTINCT acc.*, COUNT(outbox.id) AS messagecount
        FROM ' . $p . 'block_mtxt_accounts AS acc
        LEFT JOIN ' . $p . 'block_mtxt_outbox AS outbox
        ON acc.id = outbox.txttoolsaccount
        GROUP BY acc.id, acc.username, acc.password, acc.description,
        acc.defaultinbox, acc.creditsused, acc.creditsremaining, 
        acc.outboundenabled, acc.inboundenabled, acc.accounttype, acc.lastupdate'; // There has got to be a better sodding way than this!

    $mdltxt_sql['postgres7']['admingetuserlinkfrag'] = '
        moodleuser = \'%1$d\'
        AND courseid = \'%2$d\'
        AND active = %3$d';

    $mdltxt_sql['postgres7']['admingetusersonfilter'] = '
        SELECT usertable.id, usertable.username, usertable.firstname, usertable.lastname
        FROM ' . $p . 'user AS usertable
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON usertable.id = inbox.userid
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_filter AS filter
            ON filterlink.filter = filter.id
        WHERE filter.id = %1$d
        ORDER BY usertable.lastname ASC, usertable.firstname ASC';

    $mdltxt_sql['postgres7']['admingetrssrecord'] = '
        SELECT * FROM ' . $p . 'block_mtxt_rss
        WHERE expirytime > %1$d
        ORDER BY pubtime DESC
        LIMIT 1';

    $mdltxt_sql['postgres7']['adminsearchusers'] = '
        SELECT id, firstname, lastname, username
        FROM ' . $p . 'user
        WHERE deleted = 0
        AND (firstname ILIKE \'%%%1$s%%\'
        OR lastname ILIKE \'%%%1$s%%\'
        OR username ILIKE \'%%%1$s%%\')
        ORDER BY lastname ASC, firstname ASC
        LIMIT 15';

    $mdltxt_sql['postgres7']['crondeadaddressbooklinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_ab_users WHERE id IN (
            SELECT linktable.id
            FROM ' . $p . 'block_mtxt_ab_users AS linktable
            LEFT JOIN ' . $p . 'user AS usertable
                ON linktable.userid = usertable.id
            WHERE usertable.id IS NULL
        )';

    $mdltxt_sql['postgres7']['crondeadinboxlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_in_user WHERE id IN (
            SELECT linktable.id
            FROM ' . $p . 'block_mtxt_in_user AS linktable
            LEFT JOIN ' . $p . 'user AS usertable
                ON linktable.userid = usertable.id
            WHERE usertable.id IS NULL
        )';

    $mdltxt_sql['postgres7']['crondeadsentlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_sent_user WHERE id IN (
            SELECT linktable.id
            FROM ' . $p . 'block_mtxt_sent_user AS linktable
            LEFT JOIN ' . $p . 'user AS usertable
                ON linktable.userid = usertable.id
            WHERE usertable.id IS NULL
        )';

    $mdltxt_sql['postgres7']['crongetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent as sent
        INNER JOIN ' . $p . 'block_mtxt_outbox AS messages
            ON sent.messageid = messages.id
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE messages.txttoolsaccount = %1$d
        AND status.status = %2$d OR status.status = %3$d';

    $mdltxt_sql['postgres7']['crongetsentfrag'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['postgres7']['groupsgetlinkedcontacts'] = '
        SELECT contacts.id FROM ' . $p . 'block_mtxt_ab_entry AS contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem AS link
            ON contacts.id = link.contact
        WHERE link.groupid = %1$d';

    $mdltxt_sql['postgres7']['groupsgetmembers'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_grpmem
        WHERE groupid IN %1$s';

    $mdltxt_sql['postgres7']['groupsmergelinks'] = '
        UPDATE ' . $p . 'block_mtxt_ab_grpmem
        SET groupid = %1$d
        WHERE groupid = %2$d';

    $mdltxt_sql['postgres7']['inboxgetdefaultinbox'] = '
        SELECT folder.id
        FROM ' . $p . 'block_mtxt_in_folders AS folder
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_accounts AS acc
            ON acc.defaultinbox = inbox.id
        WHERE folder.name = \'Inbox\'
        AND acc.id = %1$d';

    $mdltxt_sql['postgres7']['inboxgetlinkedaccounts'] = '
        SELECT acc.*
        FROM ' . $p . 'block_mtxt_accounts AS acc
        INNER JOIN ' . $p . 'block_mtxt_filter AS filter
            ON acc.id = filter.account
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON filterlink.inbox = inbox.id
        WHERE inbox.id = %1$d
        AND acc.inboundenabled = 1';

    $mdltxt_sql['postgres7']['inboxgetinboxfilters'] = '
        SELECT filterlink.id, filter.type, filter.value, folder.id AS folderid
        FROM ' . $p . 'block_mtxt_filter AS filter
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_in_folders AS folder
            ON folder.inbox = inbox.id
        WHERE filter.account = %1$d
        AND folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY filter.id ASC';

    $mdltxt_sql['postgres7']['inboxgetinboxlist'] = '
        SELECT folder.id, usertable.firstname, usertable.lastname, usertable.username
        FROM ' . $p . 'block_mtxt_in_folders AS folder
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'user AS usertable
            ON inbox.userid = usertable.id
        WHERE folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY usertable.lastname ASC';

    $mdltxt_sql['postgres7']['inboxgetmessages'] = '
        SELECT messages.*, contacts.id AS contactid, contacts.firstname AS contactfirst, contacts.lastname AS contactlast, contacts.company AS contactcompany,
            users.id AS userid, users.firstname AS userfirst, users.lastname AS userlast, users.username
        FROM ' . $p . 'block_mtxt_in_mess AS messages
        LEFT JOIN ' . $p . 'block_mtxt_in_ab AS contactlink
            ON contactlink.receivedmessage = messages.id
        LEFT JOIN ' . $p . 'block_mtxt_ab_entry AS contacts
            ON contactlink.contact = contacts.id
        LEFT JOIN ' . $p . 'block_mtxt_in_user AS userlink
            ON userlink.receivedmessage = messages.id
        LEFT JOIN ' . $p . 'user AS users
            ON userlink.userid = users.id
        WHERE messages.folderid = %1$d
        ORDER BY %2$s
        OFFSET %3$d
        LIMIT %4$d';

    $mdltxt_sql['postgres7']['inboxmarkfolderread'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET hasbeenread = 1
        WHERE folderid = %1$d';

    $mdltxt_sql['postgres7']['inboxmovemessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE id IN (%2$s)';

    $mdltxt_sql['postgres7']['inboxmoveallmessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE folderid = %2$d';

    $mdltxt_sql['postgres7']['libgetcourseusers'] = '
        SELECT DISTINCT %1$s
        FROM ' . $p . 'user AS u
        INNER JOIN ' . $p . 'role_assignments AS r
            ON u.id  = r.userid
        WHERE r.contextid = %2$d';

    $mdltxt_sql['postgres7']['sendgetabcontacts'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE addressbook IN (%1$s)';
        
    $mdltxt_sql['postgres7']['sendgetabcontactsbyid'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE id IN (%1$s)';
        
    $mdltxt_sql['postgres7']['sendgetabgroupmembers'] = '
        SELECT DISTINCT contacts.* FROM ' . $p . 'block_mtxt_ab_entry AS contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem AS link
            ON contacts.id = link.contact
        INNER JOIN ' . $p . 'block_mtxt_ab_groups AS groups
            ON link.groupid = groups.id
        WHERE groups.id IN (%1$s)';
        
    $mdltxt_sql['postgres7']['sendgetabgroups'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_groups
        WHERE addressbook IN (%1$s)';
        
    $mdltxt_sql['postgres7']['sendgetprivateabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab AS ab
        INNER JOIN  ' .$p . 'block_mtxt_ab_users AS link
            ON ab.id = link.addressbook
        INNER JOIN ' . $p . 'user AS usertable
            ON link.userid = usertable.id
        WHERE usertable.id = %1$d
        AND ab.type = \'private\'
        AND ab.owner <> %1$d';

    $mdltxt_sql['postgres7']['sendgetpublicabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab AS ab
        WHERE ab.type = \'public\'
        OR ab.owner = %1$d';

    $mdltxt_sql['postgres7']['sendgetuserdetails'] = '
        SELECT id, username, firstname, lastname, phone1, phone2
        FROM ' . $p . 'user
        WHERE id IN (%1$s)';

    $mdltxt_sql['postgres7']['sentcountcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['postgres7']['sentcountmessages'] = '
        SELECT COUNT(o.id)
        FROM ' . $p . 'block_mtxt_outbox AS o';

    $mdltxt_sql['postgres7']['sentgetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE status.status = -1
        OR status.status = 2
        OR status.status = 5';

    $mdltxt_sql['postgres7']['sentgetunfinishedmessages1'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent';

    $mdltxt_sql['postgres7']['sentgetunfinishedmessages2'] = '
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['postgres7']['sentselectcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['postgres7']['sentselectmessages'] = '
        SELECT o.id, u.id AS moodleuserid, u.username AS moodleuser,
        acc.username AS txttoolsuser, o.messagetext, o.timesent
        FROM ' . $p . 'block_mtxt_outbox AS o
        INNER JOIN ' . $p . 'block_mtxt_accounts AS acc
            ON o.txttoolsaccount = acc.id
        INNER JOIN ' . $p . 'user AS u
            ON o.userid = u.id
        %1$s
        ORDER BY %2$s
        LIMIT %3$d
        OFFSET %4$d';

    $mdltxt_sql['postgres7']['sentcountfrag'] = '
        SELECT COUNT(*)
        FROM ' . $p . 'block_mtxt_outbox
        WHERE useraccount IN (';

    $mdltxt_sql['postgres7']['statsgetallusers'] = '
        SELECT u.id, u.username, u.firstname, u.lastname, SUM(s.numbersent) AS totalsent
        FROM ' . $p . 'block_mtxt_stats AS s
        INNER JOIN ' . $p . 'user AS u
            ON s.userid = u.id
        GROUP BY u.id, u.username, u.firstname, u.lastname
        ORDER BY totalsent DESC
        LIMIT %1$d
        OFFSET %2$d';

    $mdltxt_sql['postgres7']['statsgetalldates'] = '
        SELECT date_entered, SUM(numbersent) AS totalsent
        FROM ' . $p . 'block_mtxt_stats
        GROUP BY date_entered
        ORDER BY date_entered DESC';

    $mdltxt_sql['postgres7']['upgrade24sentmessages'] = '
        UPDATE ' . $p . 'block_mtxt_outbox
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['postgres7']['upgrade24userstats'] = '
        UPDATE ' . $p . 'block_mtxt_stats
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['postgres7']['viewgetmessagedetails'] = '
        SELECT o.*, u.id AS moodleuserid, u.username
        FROM ' . $p . 'block_mtxt_outbox AS o
        INNER JOIN ' . $p . 'user AS u
            ON o.userid = u.id
        WHERE o.id = %1$d';

    $mdltxt_sql['postgres7']['viewgetrecipients'] = '
        SELECT sent.id, sent.messageid, sent.ticketnumber, sent.destination, 
            MAX(status.updatetime) AS latestupdate
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE sent.messageid = %1$d
        GROUP BY sent.id, sent.messageid, sent.ticketnumber, sent.destination
        ORDER BY %2$s
        LIMIT %3$d
        OFFSET %4$d';

    $mdltxt_sql['postgres7']['viewgetrecipientusers'] = '
        SELECT sent.id, u.id AS userid, u.username, u.firstname, u.lastname
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_sent_user AS link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'user AS u
            ON link.userid = u.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';

    $mdltxt_sql['postgres7']['viewgetrecipientcontacts'] = '
        SELECT sent.id, contacts.id AS contactid, contacts.lastname, contacts.firstname, contacts.company
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_sent_ab AS link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'block_mtxt_ab_entry AS contacts
            ON link.contact = contacts.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';


    // MS SQL SERVER - N
    $mdltxt_sql['mssql_n']['addressmovecontacts'] = '
        UPDATE ' . $p . 'block_mtxt_ab_entry
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['mssql_n']['addressmovegroups'] = '
        UPDATE ' . $p . 'block_mtxt_ab_groups
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['mssql_n']['admingetaccandinbox'] = '
        SELECT acc.*, inbox.userid
        FROM ' . $p . 'block_mtxt_accounts AS acc
        LEFT JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON acc.defaultinbox = inbox.id
        ORDER BY acc.username ASC';

    $mdltxt_sql['mssql_n']['admingetaccountlist'] = '
        SELECT DISTINCT acc.*, COUNT(outbox.id) AS messagecount
        FROM ' . $p . 'block_mtxt_accounts AS acc
        LEFT JOIN ' . $p . 'block_mtxt_outbox AS outbox
        ON acc.id = outbox.txttoolsaccount
        GROUP BY acc.id, acc.username, acc.password, acc.description,
        acc.defaultinbox, acc.creditsused, acc.creditsremaining,
        acc.outboundenabled, acc.inboundenabled, acc.accountType, acc.lastupdate'; // There has got to be a better sodding way than this!';

    $mdltxt_sql['mssql_n']['admingetuserlinkfrag'] = '
        moodleuser = \'%1$d\'
        AND courseid = \'%2$d\'
        AND active = %3$d';

    $mdltxt_sql['mssql_n']['admingetusersonfilter'] = '
        SELECT usertable.id, usertable.username, usertable.firstname, usertable.lastname
        FROM ' . $p . 'user AS usertable
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON usertable.id = inbox.userid
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_filter AS filter
            ON filterlink.filter = filter.id
        WHERE filter.id = %1$d
        ORDER BY usertable.lastname ASC, usertable.firstname ASC';

    $mdltxt_sql['mssql_n']['admingetrssrecord'] = '
        SELECT TOP 1 * FROM ' . $p . 'block_mtxt_rss
        WHERE expirytime > %1$d
        ORDER BY pubtime DESC';

    $mdltxt_sql['mssql_n']['adminsearchusers'] = '
        SELECT TOP 15 id, firstname, lastname, username
        FROM ' . $p . 'user
        WHERE deleted = 0
        AND (firstname LIKE \'%%%1$s%%\'
        OR lastname LIKE \'%%%1$s%%\'
        OR username LIKE \'%%%1$s%%\')
        ORDER BY lastname ASC, firstname ASC';

    $mdltxt_sql['mssql_n']['crondeadaddressbooklinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_ab_users WHERE id IN (
            SELECT linktable.id
            FROM ' . $p . 'block_mtxt_ab_users AS linktable
            LEFT JOIN ' . $p . 'user AS usertable
                ON linktable.userid = usertable.id
            WHERE usertable.id IS NULL
        )';

    $mdltxt_sql['mssql_n']['crondeadinboxlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_in_user WHERE id IN (
            SELECT linktable.id
            FROM ' . $p . 'block_mtxt_in_user AS linktable
            LEFT JOIN ' . $p . 'user AS usertable
                ON linktable.userid = usertable.id
            WHERE usertable.id IS NULL
        )';

    $mdltxt_sql['mssql_n']['crondeadsentlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_sent_user WHERE id IN (
            SELECT linktable.id
            FROM ' . $p . 'block_mtxt_sent_user AS linktable
            LEFT JOIN ' . $p . 'user AS usertable
                ON linktable.userid = usertable.id
            WHERE usertable.id IS NULL
        )';

    $mdltxt_sql['mssql_n']['crongetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent as sent
        INNER JOIN ' . $p . 'block_mtxt_outbox AS messages
            ON sent.messageid = messages.id
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE messages.txttoolsaccount = %1$d
        AND status.status = %2$d OR status.status = %3$d';

    $mdltxt_sql['mssql_n']['crongetsentfrag'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['mssql_n']['groupsgetlinkedcontacts'] = '
        SELECT contacts.id FROM ' . $p . 'block_mtxt_ab_entry AS contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem AS link
            ON contacts.id = link.contact
        WHERE link.groupid = %1$d';

    $mdltxt_sql['mssql_n']['groupsgetmembers'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_grpmem
        WHERE groupid IN %1$s';

    $mdltxt_sql['mssql_n']['groupsmergelinks'] = '
        UPDATE ' . $p . 'block_mtxt_ab_grpmem
        SET groupid = %1$d
        WHERE groupid = %2$d';

    $mdltxt_sql['mssql_n']['inboxgetdefaultinbox'] = '
        SELECT folder.id
        FROM ' . $p . 'block_mtxt_in_folders AS folder
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_accounts AS acc
            ON acc.defaultinbox = inbox.id
        WHERE folder.name = \'Inbox\'
        AND acc.id = %1$d';

    $mdltxt_sql['mssql_n']['inboxgetlinkedaccounts'] = '
        SELECT acc.*
        FROM ' . $p . 'block_mtxt_accounts AS acc
        INNER JOIN ' . $p . 'block_mtxt_filter AS filter
            ON acc.id = filter.account
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON filterlink.inbox = inbox.id
        WHERE inbox.id = %1$d
        AND acc.inboundenabled = 1';

    $mdltxt_sql['mssql_n']['inboxgetinboxfilters'] = '
        SELECT filterlink.id, filter.type, filter.value, folder.id AS folderid
        FROM ' . $p . 'block_mtxt_filter AS filter
        INNER JOIN ' . $p . 'block_mtxt_in_filter AS filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_in_folders AS folder
            ON folder.inbox = inbox.id
        WHERE filter.account = %1$d
        AND folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY filter.id ASC';

    $mdltxt_sql['mssql_n']['inboxgetinboxlist'] = '
        SELECT folder.id, usertable.firstname, usertable.lastname, usertable.username
        FROM ' . $p . 'block_mtxt_in_folders AS folder
        INNER JOIN ' . $p . 'block_mtxt_inbox AS inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'user AS usertable
            ON inbox.userid = usertable.id
        WHERE folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY usertable.lastname ASC';

    $mdltxt_sql['mssql_n']['inboxgetmessages'] = '
        SELECT * FROM (
            SELECT TOP %4$d * FROM (
                SELECT TOP %5$d messages.*, contacts.id AS contactid, contacts.firstname AS contactfirst, contacts.lastname AS contactlast, contacts.company AS contactcompany,
                    users.id AS userid, users.firstname AS userfirst, users.lastname AS userlast, users.username
                FROM ' . $p . 'block_mtxt_in_mess AS messages
                LEFT JOIN ' . $p . 'block_mtxt_in_ab AS contactlink
                    ON contactlink.receivedmessage = messages.id
                LEFT JOIN ' . $p . 'block_mtxt_ab_entry AS contacts
                    ON contactlink.contact = contacts.id
                LEFT JOIN ' . $p . 'block_mtxt_in_user AS userlink
                    ON userlink.receivedmessage = messages.id
                LEFT JOIN ' . $p . 'user AS users
                    ON userlink.userid = users.id
                WHERE messages.folderid = %1$d
                ORDER BY %2$s
            ) messages ORDER BY %6$s
        ) messages ORDER BY %2$s';

    $mdltxt_sql['mssql_n']['inboxmarkfolderread'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET hasbeenread = 1
        WHERE folderid = %1$d';

    $mdltxt_sql['mssql_n']['inboxmovemessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE id IN (%2$s)';

    $mdltxt_sql['mssql_n']['inboxmoveallmessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE folderid = %2$d';

    $mdltxt_sql['mssql_n']['libgetcourseusers'] = '
        SELECT DISTINCT %1$s
        FROM ' . $p . 'user AS u
        INNER JOIN ' . $p . 'role_assignments AS r
            ON u.id  = r.userid
        WHERE r.contextid = %2$d';

    $mdltxt_sql['mssql_n']['sendgetabcontacts'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE addressbook IN (%1$s)';
        
    $mdltxt_sql['mssql_n']['sendgetabcontactsbyid'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE id IN (%1$s)';
        
    $mdltxt_sql['mssql_n']['sendgetabgroupmembers'] = '
        SELECT DISTINCT contacts.* FROM ' . $p . 'block_mtxt_ab_entry AS contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem AS link
            ON contacts.id = link.contact
        INNER JOIN ' . $p . 'block_mtxt_ab_groups AS groups
            ON link.groupid = groups.id
        WHERE groups.id IN (%1$s)';
        
    $mdltxt_sql['mssql_n']['sendgetabgroups'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_groups
        WHERE addressbook IN (%1$s)';
        
    $mdltxt_sql['mssql_n']['sendgetprivateabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab AS ab
        INNER JOIN  ' .$p . 'block_mtxt_ab_users AS link
            ON ab.id = link.addressbook
        INNER JOIN ' . $p . 'user AS usertable
            ON link.userid = usertable.id
        WHERE usertable.id = %1$d
        AND ab.type = \'private\'
        AND ab.owner <> %1$d';

    $mdltxt_sql['mssql_n']['sendgetpublicabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab AS ab
        WHERE ab.type = \'public\'
        OR ab.owner = %1$d';

    $mdltxt_sql['mssql_n']['sendgetuserdetails'] = '
        SELECT id, username, firstname, lastname, phone1, phone2
        FROM ' . $p . 'user
        WHERE id IN (%1$s)';

    $mdltxt_sql['mssql_n']['sentcountcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['mssql_n']['sentcountmessages'] = '
        SELECT COUNT(o.id)
        FROM ' . $p . 'block_mtxt_outbox AS o';

    $mdltxt_sql['mssql_n']['sentgetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_status AS status
            ON sent.ticketnumber = status.ticketnumber
        WHERE status.status = -1
        OR status.status = 2
        OR status.status = 5';

    $mdltxt_sql['mssql_n']['sentgetunfinishedmessages1'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent';

    $mdltxt_sql['mssql_n']['sentgetunfinishedmessages2'] = '
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['mssql_n']['sentselectcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['mssql_n']['sentselectmessages'] = '
        SELECT * FROM (
            SELECT TOP %1$d * FROM (
                SELECT TOP %2$d o.id, u.id AS moodleuserid, u.username AS moodleuser,
                acc.username AS txttoolsuser, o.messagetext, o.timesent
                FROM ' . $p . 'block_mtxt_outbox AS o
                INNER JOIN ' . $p . 'block_mtxt_accounts AS acc
                    ON o.txttoolsaccount = acc.id
                INNER JOIN ' . $p . 'user AS u
                    ON o.userid = u.id
                %3$s
                ORDER BY %4$s
            ) innertable ORDER BY %5$s
        ) outertable ORDER BY %6$s';

    $mdltxt_sql['mssql_n']['sentcountfrag'] = '
        SELECT COUNT(*)
        FROM ' . $p . 'block_mtxt_outbox
        WHERE useraccount IN (';

    $mdltxt_sql['mssql_n']['statsgetallusers'] = '
        SELECT * FROM (
            SELECT TOP %1$d * FROM (
                SELECT TOP %3$d u.id, u.username, u.firstname, u.lastname, SUM(s.numbersent) AS totalsent
                FROM ' . $p . 'block_mtxt_stats AS s
                INNER JOIN ' . $p . 'user AS u
                    ON s.userid = u.id
                GROUP BY u.id, u.username, u.firstname, u.lastname
                ORDER BY totalsent DESC
            ) innertable ORDER BY totalsent ASC
        ) outertable ORDER BY totalsent DESC';

    $mdltxt_sql['mssql_n']['statsgetalldates'] = '
        SELECT date_entered, SUM(numbersent) AS totalsent
        FROM ' . $p . 'block_mtxt_stats
        GROUP BY date_entered
        ORDER BY date_entered DESC';

    $mdltxt_sql['mssql_n']['upgrade24sentmessages'] = '
        UPDATE ' . $p . 'block_mtxt_outbox
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['mssql_n']['upgrade24userstats'] = '
        UPDATE ' . $p . 'block_mtxt_stats
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['mssql_n']['viewgetmessagedetails'] = '
        SELECT o.*, u.id AS moodleuserid, u.username
        FROM ' . $p . 'block_mtxt_outbox AS o
        INNER JOIN ' . $p . 'user AS u
            ON o.userid = u.id
        WHERE o.id = %1$d';

    $mdltxt_sql['mssql_n']['viewgetrecipients'] = '
        SELECT * FROM (
            SELECT TOP %1$d * FROM  (
                SELECT TOP %2$d sent.id, sent.messageid, sent.ticketnumber, sent.destination, 
                    MAX(status.updatetime) AS latestupdate
                    FROM ' . $p . 'block_mtxt_sent AS sent
                    INNER JOIN ' . $p . 'block_mtxt_status AS status
                        ON sent.ticketnumber = status.ticketnumber
                    WHERE sent.messageid = %3$d
                    GROUP BY sent.id, sent.messageid, sent.ticketnumber, sent.destination
                    ORDER BY %4$s
            ) innertable ORDER BY %5$s
        ) outertable ORDER BY %6$s';

    $mdltxt_sql['mssql_n']['viewgetrecipientusers'] = '
        SELECT sent.id, usertable.id AS userid, usertable.username,
        usertable.firstname, usertable.lastname
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_sent_user AS link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'user AS usertable
            ON link.userid = usertable.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';

    $mdltxt_sql['mssql_n']['viewgetrecipientcontacts'] = '
        SELECT sent.id, contacts.id AS contactid, contacts.lastname, contacts.firstname, contacts.company
        FROM ' . $p . 'block_mtxt_sent AS sent
        INNER JOIN ' . $p . 'block_mtxt_sent_ab AS link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'block_mtxt_ab_entry AS contacts
            ON link.contact = contacts.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';


    // Oracle
    $mdltxt_sql['oci8po']['addressmovecontacts'] = '
        UPDATE ' . $p . 'block_mtxt_ab_entry
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['oci8po']['addressmovegroups'] = '
        UPDATE ' . $p . 'block_mtxt_ab_groups
        SET addressbook = %1$d
        WHERE addressbook = %2$d';

    $mdltxt_sql['oci8po']['admingetaccandinbox'] = '
        SELECT acc.*, inbox.userid
        FROM ' . $p . 'block_mtxt_accounts acc
        LEFT JOIN ' . $p . 'block_mtxt_inbox inbox
            ON acc.defaultinbox = inbox.id
        ORDER BY acc.username ASC';

    $mdltxt_sql['oci8po']['admingetaccountlist'] = '
        SELECT DISTINCT acc.*, COUNT(outbox.id) AS messagecount
        FROM ' . $p . 'block_mtxt_accounts acc
        LEFT JOIN ' . $p . 'block_mtxt_outbox outbox
        ON acc.id = outbox.txttoolsaccount
        GROUP BY acc.id, acc.username, acc.password, acc.description,
        acc.defaultinbox, acc.creditsused, acc.creditsremaining,
        acc.outboundenabled, acc.inboundenabled, acc.accountType, acc.lastupdate'; // There has got to be a better sodding way than this!

    $mdltxt_sql['oci8po']['admingetuserlinkfrag'] = '
        moodleuser = \'%1$d\'
        AND courseid = \'%2$d\'
        AND active = %3$d';

    $mdltxt_sql['oci8po']['admingetusersonfilter'] = '
        SELECT usertable.id, usertable.username, usertable.firstname, usertable.lastname
        FROM ' . $p . 'user usertable
        INNER JOIN ' . $p . 'block_mtxt_inbox inbox
            ON usertable.id = inbox.userid
        INNER JOIN ' . $p . 'block_mtxt_in_filter filterlink
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_filter filter
            ON filterlink.filter = filter.id
        WHERE filter.id = %1$d
        ORDER BY usertable.lastname ASC, usertable.firstname ASC';

    $mdltxt_sql['oci8po']['admingetrssrecord'] = '
        SELECT * FROM (
            SELECT * FROM ' . $p . 'block_mtxt_rss
            WHERE expirytime > %1$d
            ORDER BY pubtime DESC
        ) WHERE ROWNUM = 1';

    $mdltxt_sql['oci8po']['adminsearchusers'] = '
        SELECT id, firstname, lastname, username
        FROM ' . $p . 'user
        WHERE deleted = 0
        AND (lower(firstname) LIKE lower(\'%%%1$s%%\')
        OR lower(lastname) LIKE lower(\'%%%1$s%%\')
        OR lower(username) LIKE lower(\'%%%1$s%%\'))
        AND ROWNUM <= 15
        ORDER BY lastname ASC, firstname ASC';

    $mdltxt_sql['oci8po']['crondeadaddressbooklinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_ab_users WHERE id IN (
            SELECT id FROM (
                SELECT linktable.id
                FROM ' . $p . 'block_mtxt_ab_users linktable
                LEFT JOIN ' . $p . 'user usertable
                    ON linktable.userid = usertable.id
                WHERE usertable.id IS NULL
            ) blahblah
        )';

    $mdltxt_sql['oci8po']['crondeadinboxlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_in_user WHERE id IN (
            SELECT id FROM (
                SELECT linktable.id
                FROM ' . $p . 'block_mtxt_in_user linktable
                LEFT JOIN ' . $p . 'user usertable
                    ON linktable.userid = usertable.id
                WHERE usertable.id IS NULL
            ) blahblah
        )';

    $mdltxt_sql['oci8po']['crondeadsentlinks'] = '
        DELETE FROM ' . $p . 'block_mtxt_sent_user WHERE id IN (
            SELECT id FROM (
                SELECT linktable.id
                FROM ' . $p . 'block_mtxt_sent_user linktable
                LEFT JOIN ' . $p . 'user usertable
                    ON linktable.userid = usertable.id
                WHERE usertable.id IS NULL
            ) blahblah
        )';

    $mdltxt_sql['oci8po']['crongetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent sent
        INNER JOIN ' . $p . 'block_mtxt_outbox messages
            ON sent.messageid = messages.id
        INNER JOIN ' . $p . 'block_mtxt_status status
            ON sent.ticketnumber = status.ticketnumber
        WHERE messages.txttoolsaccount = %1$d
        AND status.status = %2$d OR status.status = %3$d';

    $mdltxt_sql['oci8po']['crongetsentfrag'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['oci8po']['groupsgetlinkedcontacts'] = '
        SELECT contacts.id FROM ' . $p . 'block_mtxt_ab_entry contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem link
            ON contacts.id = link.contact
        WHERE link.groupid = %1$d';

    $mdltxt_sql['oci8po']['groupsgetmembers'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_grpmem
        WHERE groupid IN %1$s';

    $mdltxt_sql['oci8po']['groupsmergelinks'] = '
        UPDATE ' . $p . 'block_mtxt_ab_grpmem
        SET groupid = %1$d
        WHERE groupid = %2$d';

    $mdltxt_sql['oci8po']['inboxgetdefaultinbox'] = '
        SELECT folder.id
        FROM ' . $p . 'block_mtxt_in_folders folder
        INNER JOIN ' . $p . 'block_mtxt_inbox inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_accounts acc
            ON acc.defaultinbox = inbox.id
        WHERE folder.name = \'Inbox\'
        AND acc.id = %1$d';

    $mdltxt_sql['oci8po']['inboxgetlinkedaccounts'] = '
        SELECT acc.*
        FROM ' . $p . 'block_mtxt_accounts acc
        INNER JOIN ' . $p . 'block_mtxt_filter filter
            ON acc.id = filter.account
        INNER JOIN ' . $p . 'block_mtxt_in_filter filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox inbox
            ON filterlink.inbox = inbox.id
        WHERE inbox.id = %1$d
        AND acc.inboundenabled = 1';

    $mdltxt_sql['oci8po']['inboxgetinboxfilters'] = '
        SELECT filterlink.id, filter.type, filter.value, folder.id AS folderid
        FROM ' . $p . 'block_mtxt_filter filter
        INNER JOIN ' . $p . 'block_mtxt_in_filter filterlink
            ON filter.id = filterlink.filter
        INNER JOIN ' . $p . 'block_mtxt_inbox inbox
            ON filterlink.inbox = inbox.id
        INNER JOIN ' . $p . 'block_mtxt_in_folders folder
            ON folder.inbox = inbox.id
        WHERE filter.account = %1$d
        AND folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY filter.id ASC';

    $mdltxt_sql['oci8po']['inboxgetinboxlist'] = '
        SELECT folder.id, usertable.firstname, usertable.lastname, usertable.username
        FROM ' . $p . 'block_mtxt_in_folders folder
        INNER JOIN ' . $p . 'block_mtxt_inbox inbox
            ON folder.inbox = inbox.id
        INNER JOIN ' . $p . 'user usertable
            ON inbox.userid = usertable.id
        WHERE folder.name = \'Inbox\'
        AND folder.candelete = 0
        ORDER BY usertable.lastname ASC';

    $mdltxt_sql['oci8po']['inboxgetmessages'] = '
        SELECT * FROM (
            SELECT * FROM (
                SELECT messages.*, contacts.id AS contactid, contacts.firstname AS contactfirst, contacts.lastname AS contactlast, contacts.company AS contactcompany,
                    users.id AS userid, users.firstname AS userfirst, users.lastname AS userlast, users.username
                FROM ' . $p . 'block_mtxt_in_mess messages
                LEFT JOIN ' . $p . 'block_mtxt_in_ab contactlink
                    ON contactlink.receivedmessage = messages.id
                LEFT JOIN ' . $p . 'block_mtxt_ab_entry contacts
                    ON contactlink.contact = contacts.id
                LEFT JOIN ' . $p . 'block_mtxt_in_user userlink
                    ON userlink.receivedmessage = messages.id
                LEFT JOIN ' . $p . 'user users
                    ON userlink.userid = users.id
                WHERE messages.folderid = %1$d
                ORDER BY %2$s
            ) WHERE ROWNUM >= %3$d
        ) WHERE ROWNUM <= %4$d';

    $mdltxt_sql['oci8po']['inboxmarkfolderread'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET hasbeenread = 1
        WHERE folderid = %1$d';

    $mdltxt_sql['oci8po']['inboxmovemessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE id IN (%2$s)';

    $mdltxt_sql['oci8po']['inboxmoveallmessages'] = '
        UPDATE ' . $p . 'block_mtxt_in_mess
        SET folderid = %1$d
        WHERE folderid = %2$d';

    $mdltxt_sql['oci8po']['libgetcourseusers'] = '
        SELECT DISTINCT %1$s
        FROM ' . $p . 'user u
        INNER JOIN ' . $p . 'role_assignments r
            ON u.id  = r.userid
        WHERE r.contextid = %2$d';

    $mdltxt_sql['oci8po']['sendgetabcontacts'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE addressbook IN (%1$s)';

    $mdltxt_sql['oci8po']['sendgetabcontactsbyid'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_entry
        WHERE id IN (%1$s)';

    $mdltxt_sql['oci8po']['sendgetabgroupmembers'] = '
        SELECT DISTINCT contacts.* FROM ' . $p . 'block_mtxt_ab_entry contacts
        INNER JOIN ' . $p . 'block_mtxt_ab_grpmem link
            ON contacts.id = link.contact
        INNER JOIN ' . $p . 'block_mtxt_ab_groups groups
            ON link.groupid = groups.id
        WHERE groups.id IN (%1$s)';

    $mdltxt_sql['oci8po']['sendgetabgroups'] = '
        SELECT * FROM ' . $p . 'block_mtxt_ab_groups
        WHERE addressbook IN (%1$s)';

    $mdltxt_sql['oci8po']['sendgetprivateabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab ab
        INNER JOIN  ' .$p . 'block_mtxt_ab_users link
            ON ab.id = link.addressbook
        INNER JOIN ' . $p . 'user usertable
            ON link.userid = usertable.id
        WHERE usertable.id = %1$d
        AND ab.type = \'private\'
        AND ab.owner <> %1$d';

    $mdltxt_sql['oci8po']['sendgetpublicabs'] = '
        SELECT ab.* FROM ' . $p . 'block_mtxt_ab ab
        WHERE ab.type = \'public\'
        OR ab.owner = %1$d';

    $mdltxt_sql['oci8po']['sendgetuserdetails'] = '
        SELECT id, username, firstname, lastname, phone1, phone2
        FROM ' . $p . 'user
        WHERE id IN (%1$s)';

    $mdltxt_sql['oci8po']['sentcountcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['oci8po']['sentcountmessages'] = '
        SELECT COUNT(id)
        FROM ' . $p . 'block_mtxt_outbox o';

    $mdltxt_sql['oci8po']['sentgetfinishedmessages'] = '
        SELECT sent.id, sent.ticketnumber
        FROM ' . $p . 'block_mtxt_sent sent
        INNER JOIN ' . $p . 'block_mtxt_status status
            ON sent.ticketnumber = status.ticketnumber
        WHERE status.status = -1
        OR status.status = 2
        OR status.status = 5';

    $mdltxt_sql['oci8po']['sentgetunfinishedmessages1'] = '
        SELECT id, ticketnumber
        FROM ' . $p . 'block_mtxt_sent';

    $mdltxt_sql['oci8po']['sentgetunfinishedmessages2'] = '
        WHERE ticketnumber NOT IN (\'';

    $mdltxt_sql['oci8po']['sentselectcriteria'] = '
        WHERE o.timesent >= %1$d
        AND o.timesent < %2$d';

    $mdltxt_sql['oci8po']['sentselectmessages'] = '
        SELECT * FROM (
            SELECT * FROM (
                SELECT o.id, u.id AS moodleuserid, u.username AS moodleuser,
                acc.username AS txttoolsuser, o.messagetext, o.timesent
                FROM ' . $p . 'block_mtxt_outbox o
                INNER JOIN ' . $p . 'block_mtxt_accounts acc
                    ON o.txttoolsaccount = acc.id
                INNER JOIN ' . $p . 'user u
                    ON o.userid = u.id
                %1$s
                ORDER BY %2$s
            ) WHERE ROWNUM >= %4$d
        ) WHERE ROWNUM <= %3$d';

    $mdltxt_sql['oci8po']['sentcountfrag'] = '
        SELECT COUNT(*)
        FROM ' . $p . 'block_mtxt_outbox
        WHERE useraccount IN (';

    $mdltxt_sql['oci8po']['statsgetallusers'] = '
        SELECT * FROM (
            SELECT * FROM (
                SELECT u.id, u.username, u.firstname, u.lastname, SUM(s.numbersent) AS totalsent
                FROM ' . $p . 'block_mtxt_stats s
                INNER JOIN ' . $p . 'user u
                    ON s.userid = u.id
                GROUP BY u.id, u.username, u.firstname, u.lastname
                ORDER BY totalsent DESC
            ) WHERE ROWNUM >= %2$d
        ) WHERE ROWNUM <= %1$d';

    $mdltxt_sql['oci8po']['statsgetalldates'] = '
        SELECT date_entered, SUM(numbersent) AS totalsent
        FROM ' . $p . 'block_mtxt_stats
        GROUP BY date_entered
        ORDER BY date_entered DESC';

    $mdltxt_sql['oci8po']['upgrade24sentmessages'] = '
        UPDATE ' . $p . 'block_mtxt_outbox
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['oci8po']['upgrade24userstats'] = '
        UPDATE ' . $p . 'block_mtxt_stats
        SET userid = %1$d, txttoolsaccount = %2$d
        WHERE useraccount = %3$d';

    $mdltxt_sql['oci8po']['viewgetmessagedetails'] = '
        SELECT o.*, u.id AS moodleuserid, u.username
        FROM ' . $p . 'block_mtxt_outbox o
        INNER JOIN ' . $p . 'user u
            ON o.userid = u.id
        WHERE o.id = %1$d';

    $mdltxt_sql['oci8po']['viewgetrecipients'] = '
        SELECT * FROM (
            SELECT * FROM (
                SELECT sent.id, sent.messageid, sent.ticketnumber, sent.destination,
                    MAX(status.updatetime) AS latestupdate
                FROM ' . $p . 'block_mtxt_sent sent
                INNER JOIN ' . $p . 'block_mtxt_status status
                    ON sent.ticketnumber = status.ticketnumber
                WHERE sent.messageid = %1$d
                GROUP BY sent.id, sent.messageid, sent.ticketnumber, sent.destination
                ORDER BY %2$s
            ) WHERE ROWNUM >= %4$d
        ) WHERE ROWNUM <= %3$d';


    $mdltxt_sql['oci8po']['viewgetrecipientusers'] = '
        SELECT sent.id, usertable.id AS userid, usertable.username, usertable.firstname, usertable.lastname
        FROM ' . $p . 'block_mtxt_sent sent
        INNER JOIN ' . $p . 'block_mtxt_sent_user link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'user usertable
            ON link.userid = usertable.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';

    $mdltxt_sql['oci8po']['viewgetrecipientcontacts'] = '
        SELECT sent.id, contacts.id AS contactid, contacts.lastname, contacts.firstname, contacts.company
        FROM ' . $p . 'block_mtxt_sent sent
        INNER JOIN ' . $p . 'block_mtxt_sent_ab link
            ON sent.id = link.sentmessage
        INNER JOIN ' . $p . 'block_mtxt_ab_entry contacts
            ON link.contact = contacts.id
        WHERE sent.messageid = %1$d
        AND sent.id IN %2$s';

    // Alias types together
    $mdltxt_sql['mysqli']       = $mdltxt_sql['mysql'];
    $mdltxt_sql['mssql']        = $mdltxt_sql['mssql_n'];
    $mdltxt_sql['odbc_mssql']   = $mdltxt_sql['mssql_n'];

    $GLOBALS['mdltxt_sql'] = $mdltxt_sql;  // Necessary to get around problems with variable scope in upgrade script


    function moodletxt_get_sql($sqlid) {

        global $CFG;

        // Check that the DB type given is supported
        if (! array_key_exists($CFG->dbtype, $GLOBALS['mdltxt_sql']))
            return false;

        // Check that the SQL id being fetched exists
        if (! array_key_exists($sqlid, $GLOBALS['mdltxt_sql'][$CFG->dbtype]))
            return false;


        // Get SQL and return it
        return trim($GLOBALS['mdltxt_sql'][$CFG->dbtype][$sqlid]);

    }

?>
