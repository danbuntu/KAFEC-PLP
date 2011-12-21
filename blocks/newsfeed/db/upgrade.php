<?php

function xmldb_block_newsfeed_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    // Define 'special' newsfeed holding course shortname
    define('NEWSFEEDSCOURSE', 'Newsfeeds');

    $result = true;

/// And upgrade begins here. For each one, you'll need one
/// block of code similar to the next one. Please, delete
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }
    if ($result && $oldversion < 2007051100) {
    /// Rename field public on table newsfeed to publicfeed
        $table = new XMLDBTable('newsfeed');
        $field = new XMLDBField('public');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'summary');

    /// Launch rename field public
        $result = $result && rename_field($table, $field, 'publicfeed');

    /// Rename field date on table newsfeed_versions to appearancedate
        $table = new XMLDBTable('newsfeed_versions');
        $field = new XMLDBField('date');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'entryid');

    /// Launch rename field date
        $result = $result && rename_field($table, $field, 'appearancedate');

    /// Rename field size on table newsfeed_files to filesize
        $table = new XMLDBTable('newsfeed_files');
        $field = new XMLDBField('size');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null, 'mimetype');

    /// Launch rename field size
        $result = $result && rename_field($table, $field, 'filesize');
    }

    if ($result && $oldversion < 2007051101) {

        // Add newsfeed table blockinstance field if it does not exist
        $table = new XMLDBTable('newsfeed');
        if (table_exists($table)) {

            $tw=new transaction_wrapper();

            // Add blockinstance field
            $field = new XMLDBField('blockinstance');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'folderid');
            if (!field_exists($table, $field)) {
                $result = $result && add_field($table, $field);
            }

            $tw->complete($result);
        }

        // Add newsfeeds course if it does not exist
        if ($result && !get_field('course', 'category', 'shortname', 'Newsfeeds')) {
            $result = false;
            $course = new stdClass();
            $course->category = 0;
            $course->fullname = 'DO NOT DELETE - Newsfeed block holding course';
            $course->shortname = 'Newsfeeds';
            $course->summary = 'This course contains all newsfeeds not allocated to a specific course';
            $course->visible = 0;
            if (($catid = get_field('course_categories', 'id', 'name', 'Miscellaneous'))) {
                $course->category = $catid;
            } else if (($catid = get_field('course_categories', 'id', 'name', 'Preloaded'))) {
                $course->category = $catid;
            } else {
                $course->category = get_field_sql('SELECT MIN(id) FROM '.$CFG->prefix.'course_categories');
            }
            $result = create_course($course);
        }

        if ($result) {
            // Surround newsfeed table changes by a transaction
            $tw = new transaction_wrapper();

            try {

                // newsfeed table added to query to ignore newfeeds/blocks that have already been updated
                $sql = "
SELECT bi.id, bi.blockid, bi.pageid, bi.pagetype, bi.configdata, c.id as courseid, nf.id as nfid
 FROM {$CFG->prefix}block_instance bi
 INNER JOIN {$CFG->prefix}block b ON bi.blockid = b.id
 LEFT OUTER JOIN {$CFG->prefix}course c ON bi.pageid = c.id
 LEFT OUTER JOIN {$CFG->prefix}newsfeed nf ON bi.id = nf.blockinstance
 WHERE b.name = 'newsfeed'
 AND (nf.id IS NULL OR nf.blockinstance IS NULL)
 ORDER BY bi.pagetype, bi.pageid;
";

                // Get newsfeed blocks to be checked
                $rs = get_recordset_sql($sql);

                // Error? if query failed, as something seriously wrong
                if (!$rs) {
                    throw new Exception('ERROR: newsfeed block instances query failed');
                }
                print_object('newsfeed block instances to process = '.$rs->RecordCount());

                // Define a few variables for recording statistics
                $pagetypes = array();

                $xnoconfigdataids = array();
                $xnonewsfeedids = array();
                $xwithnewsfeedids = 0;

                $nocourseids = array();
                $noconfigdataids = array();
                $nonewsfeedids = array();
                $withnewsfeedids = 0;
                $binewsfeedids = array();

                // Pick up newsfeed block id in case need to add block instances
                if ($rs->RecordCount() == 0) {
                    if (!($newsfeedblockid = get_field('block', 'id', 'name', 'newsfeed'))) {
                        throw new Exception('ERROR: newsfeed block query failed');
                    }
                } else {
                    $newsfeedblockid = $rs->fields['blockid'];
                }

                // Loop through all the newsfeed block instances
                while (!$rs->EOF) {
                    // Pick up block instance id (used as array key for some of following arrays)
                    $blockinstanceid = $rs->fields['id'];

                    // Build array of page types
                    $pagetype = $rs->fields['pagetype'];
                    $pageid = $rs->fields['pageid'];
                    if (!array_key_exists($pagetype, $pagetypes)) {
                        $pagetypes[$pagetype] = 1;
                    } else {
                        $pagetypes[$pagetype]++;
                    }

                    // Decode config data
                    $configdata = $rs->fields['configdata'];

                    // Check course-view and non course-view block instances
                    if ($pagetype != 'course-view') {
                        if (empty($configdata)) {
                            $xnoconfigdataids[$blockinstanceid] = $pagetype.','.$pageid;
                        } else {
                            $nfconfigdata = unserialize(base64_decode($configdata));
                            if (empty($nfconfigdata->newsfeedid)) {
                                $xnonewsfeedids[$blockinstanceid] = $pagetype.','.$pageid;
                            } else {
                                $xwithnewsfeedids++;
                            }
                        }
                    } else {
                        $courseid = $rs->fields['courseid'];
                        if (empty($pageid) || empty($courseid)) {
                            $nocourseids[$blockinstanceid] = $pagetype.','.$pageid.','.$courseid;
                        } else if (empty($configdata)) {
                            $noconfigdataids[$blockinstanceid] = $pagetype.','.$courseid;
                        } else {
                            $nfconfigdata = unserialize(base64_decode($configdata));
                            if (empty($nfconfigdata->newsfeedid)) {
                                $nonewsfeedids[$blockinstanceid] = $pagetype.','.$courseid;
                            } else {
                                $withnewsfeedids++;
                                if (!array_key_exists($nfconfigdata->newsfeedid, $binewsfeedids)) {
                                    $blockinstances = array();
                                } else {
                                    $blockinstances = $binewsfeedids[$nfconfigdata->newsfeedid];
                                }
                                $blockinstances[$blockinstanceid] = $nfconfigdata;
                                $binewsfeedids[$nfconfigdata->newsfeedid] = $blockinstances;
                            }
                        }
                    }

                    // Get next newsfeed block instance to be checked
                    $rs->MoveNext();
                } // end while(!$rs->EOF)

                foreach ($pagetypes as $pagetype => $count) {
                    print_object($pagetype.' = '.$count);
                }

                // Get all the newsfeeds for later
                // Could get just the ones we want later, but lets see which ones left
                if (!($newsfeeds = get_records('newsfeed')) ){
                    $newsfeeds = array();
                }

                // Again ignore newfeeds/blocks that have already been updated
                foreach ($newsfeeds as $key => $value) {
                    if (!empty($value->blockinstance)) {
                        unset($newsfeeds[$key]);
                    }
                }
                print_object('newsfeeds to process = '.count($newsfeeds));
                print_object('non course-view block instances with no configdata = '.count($xnoconfigdataids));
                print_object('non course-view block instances with configdata but no newsfeed id = '.count($xnonewsfeedids));
                print_object('non course-view block instances with configdata and newsfeed id = '.$xwithnewsfeedids);
                print_object('block instances with no/missing course = '.count($nocourseids));
                print_object('course-view block instances with no configdata = '.count($noconfigdataids));
                print_object('course-view block instances with configdata but no newsfeed id = '.count($nonewsfeedids));
                print_object('course-view block instances with configdata and newsfeed id = '.$withnewsfeedids);

                // Process block instance newsfeed ids
                // NOTE: newsfeed can be flagged as deleted, but block instance may still exist!!!
                ksort($binewsfeedids);
                $j = 0;
                $k = 0;
                $l = 0;

                // Newsfeed object for updating newsfeeds with block instance id
                $objnewsfeed = new stdClass();

                // configdata object for storing in new block instances
                $newconfigdata = new stdClass();
                $newconfigdata->newsfeedid = 0;
                $newconfigdata->showcount = 3;
                $newconfigdata->showsummaries = 1;

                // Block Instance object for inserting block instances
                // Note: Blocks are not pinned, therefore ignore this option for pageid and weight
                $newblockinstance = new stdClass();
                $newblockinstance->blockid = $newsfeedblockid;
                $newblockinstance->pageid = get_field('course', 'id', 'shortname', NEWSFEEDSCOURSE);
                if (!$newblockinstance->pageid) {
                    throw new Exception('Unable to locate newsfeed holding course');
                }
                $newblockinstance->pagetype = PAGE_COURSE_VIEW;
                $newblockinstance->position = BLOCK_POS_RIGHT;
                $sql = 'SELECT 1, max(weight) + 1 AS nextfree' .
                       ' FROM '.$CFG->prefix.'block_instance' .
                       ' WHERE pageid = '.$newblockinstance->pageid.
                       ' AND pagetype = \''.$newblockinstance->pagetype.'\'' .
                       ' AND position = \''. $newblockinstance->position .'\'';
                $weight = get_record_sql($sql);
                $newblockinstance->weight = empty($weight->nextfree) ? 0 : $weight->nextfree;
                $newblockinstance->visible = 0;

                // Block Instance object for updating current block instances
                $curblockinstance = new stdClass();

                foreach ($binewsfeedids as $newsfeedid => $blockinstances) {
                    $n = count($blockinstances);
                    if ($n < 2) {

                        $j++;

                        // Newsfeed in one and only one block
                        // Update newsfeed table with block instance id
                        $objnewsfeed->id = $newsfeedid;
                        $objnewsfeed->blockinstance = key($blockinstances);
                        if (!update_record('newsfeed', $objnewsfeed)) {
                            throw new Exception('Error updating newsfeed id = '.$newsfeedid.' with blockinstance = '.$objnewsfeed->blockinstance);
                        }
                        unset($binewsfeedids[$newsfeedid]);
                    } else {

                        $k++;
                        $l += $n;

                        // Newsfeed in more than one block
                        // Add new block instance for newsfeed
                        unset($newblockinstance->id);
                        $newconfigdata->newsfeedid = $newsfeedid;
                        $newblockinstance->configdata = base64_encode(serialize($newconfigdata));
                        if (!($newblockinstance->id = insert_record('block_instance', $newblockinstance))) {
                            throw new Exception('Error adding new block instance for newsfeed id = '.$newsfeedid);
                        }
                        $newblockinstance->weight++;

                        // Update newsfeed table with new block instance id
                        $objnewsfeed->id = $newsfeedid;
                        $objnewsfeed->blockinstance = $newblockinstance->id;
                        if (!update_record('newsfeed', $objnewsfeed)) {
                            throw new Exception('Error updating newsfeed id = '.$newsfeedid.' with blockinstance = '.$newblockinstance->id);
                        }

                        // For each existing block instance
                        // The original newsfeed is the same for all blocks
                        if (!isset($newsfeeds[$newsfeedid])) {
                            throw new Exception('Error newsfeed id = '.$newsfeedid.' for blockinstance = '.$newblockinstance->id.' does not exist');
                        }
                        $newsfeed = addslashes_recursive($newsfeeds[$newsfeedid]);

                        foreach ($blockinstances as $curblockinstanceid => $curconfigdata) {

                            // Insert new newsfeed for each existing block instance
                            unset($newsfeed->id);
                            $newsfeed->blockinstance = $curblockinstanceid;
                            if (!($newsfeed->id = insert_record('newsfeed', $newsfeed))) {
                                throw new Exception('Error adding new newsfeed for blockinstance = '.$curblockinstanceid);
                            }

                            // Set new newsfeed id in current block instance
                            $curblockinstance->id = $curblockinstanceid;
                            $curconfigdata->newsfeedid = $newsfeed->id;
                            $curblockinstance->configdata = base64_encode(serialize($curconfigdata));
                            if (!update_record('block_instance', $curblockinstance)) {
                                throw new Exception('Error updating blockinstance = '.$curblockinstanceid.' with new newsfeed');
                            }

                            // Insert new newsfeed for each existing block instance
                            $sql = "INSERT INTO {$CFG->prefix}newsfeed_includes (parentnewsfeedid, childnewsfeedid) VALUES({$newsfeed->id}, {$newsfeedid})";
                            if (!execute_sql($sql, false)) {
                                throw new Exception('Error adding new newsfeed include of ('.$newsfeed->id.', '.$newsfeedid.')');
                            }
                        }
                    }
                    unset($newsfeeds[$newsfeedid]);
                }
                print_object( 'block instances with configdata with unique newsfeed id = '.$j);
                print_object( 'block instances with configdata with non unique newsfeed id = '.$l);
                print_object( 'non unique newsfeed ids = '.$k);
                print_object('newsfeeds still to be processed = '.count($newsfeeds));

                // Process newsfeeds without any block instances
                $j = 0;
                $k = 0;

                // Update block Instance object for inserting block instances to the other side
                $newblockinstance->position = BLOCK_POS_LEFT;
                $sql = 'SELECT 1, max(weight) + 1 AS nextfree' .
                       ' FROM '.$CFG->prefix.'block_instance' .
                       ' WHERE pageid = '.$newblockinstance->pageid.
                       ' AND pagetype = \''.$newblockinstance->pagetype.'\'' .
                       ' AND position = \''. $newblockinstance->position .'\'';
                $weight = get_record_sql($sql);
                $newblockinstance->weight = empty($weight->nextfree) ? 0 : $weight->nextfree;

                foreach ($newsfeeds as $key => $newsfeed) {

                    if ($newsfeed->deleted) {

                        // Ignore deleted newsfeeds for now
                        $j++;

                    } else {

                        $k++;

                        // Add new block instance for newsfeed (if newsfeed has not been deleted)
                        unset($newblockinstance->id);
                        $newconfigdata->newsfeedid = $newsfeed->id;
                        $newblockinstance->configdata = base64_encode(serialize($newconfigdata));
                        if (!($newblockinstance->id = insert_record('block_instance', $newblockinstance))) {
                            throw new Exception('Error adding new block instance for newsfeed id = '.$newsfeedid);
                        }
                        $newblockinstance->weight++;

                        // Update newsfeed table with new block instance id
                        $objnewsfeed->id = $newsfeed->id;
                        $objnewsfeed->blockinstance = $newblockinstance->id;
                        if (!update_record('newsfeed', $objnewsfeed)) {
                            throw new Exception('Error updating newsfeed id = '.$newsfeedid.' with blockinstance = '.$newblockinstance->id);
                        }
                    }
                }
                print_object('newsfeeds added to new block instance = '.$k);
                print_object('newsfeeds marked as deleted so not added to new block instance = '.$j);

            } catch (Exception $e) {

                // Fail transaction
                $result = false;

                // Display exception
                print_object('Exception: '.$e->getMessage());
            }

            // Complete transaction
            $tw->complete($result);
        }
    }

    if ($result && $oldversion < 2008022900) {

        // Note: Continue on error as can be done manually if required
        // Delete newsfeed poster role if it exists
        $name = 'Newsfeed poster';
        $shortname = 'newsfeedposter';
        if (($role = get_record('role', 'name', $name)) ||
            ($role = get_record('role', 'shortname', $shortname))) {

            $tw=new transaction_wrapper();

            $res = delete_role($role->id);

            $tw->complete($res);
        }
        
        // Delete newsfeed approver role if it exists
        $name = 'Newsfeed approver';
        $shortname = 'newsfeedapprover';
        if (($role = get_record('role', 'name', $name)) ||
            ($role = get_record('role', 'shortname', $shortname))) {

            $tw=new transaction_wrapper();

            $res = delete_role($role->id);

            $tw->complete($res);
        }
        
        // Add newsfeed poster and approver capabilities to course team updater role if it exists
        $name = 'Course team updater';
        $shortname = 'courseteamupdater';
        if (($role = get_record('role', 'name', $name)) ||
            ($role = get_record('role', 'shortname', $shortname))) {
            if ($sitecontext = get_context_instance(CONTEXT_SYSTEM, SITEID)) {

                // Check role has newsfeed approve capability (assign if not)
                $cap = 'block/newsfeed:approve';
                if (!($rcap = get_record('role_capabilities', 'roleid', $role->id,
                                                              'capability', $cap,
                                                              'contextid', $sitecontext->id))) {
                    assign_capability($cap, CAP_ALLOW, $role->id, $sitecontext->id);
                }

                // Check role has newsfeed post capability (assign if not)
                $cap = 'block/newsfeed:post';
                if (!($rcap = get_record('role_capabilities', 'roleid', $role->id,
                                                              'capability', $cap,
                                                              'contextid', $sitecontext->id))) {
                    assign_capability($cap, CAP_ALLOW, $role->id, $sitecontext->id);
                }
            }
        }
    }
    
    if ($result && $oldversion < 2008061704) {
        // Drop newsfeed folders table if it exists
        $table = new XMLDBTable('newsfeed_folders');
        if (table_exists($table)) {

            $tw=new transaction_wrapper();

            // Drop newsfeed folders table
            $result = drop_table($table, false, false);

            $tw->complete($result);
        }

        // Drop newsfeed table pres field if it exists
        $table = new XMLDBTable('newsfeed');
        $field = new XMLDBField('pres');
        if (field_exists($table, $field)) {

            $tw=new transaction_wrapper();

            // Drop newsfeed table 'pres' field
            $result = drop_field($table, $field, false, false);

            $tw->complete($result);
        }

        // Drop newsfeed table folderid field if it exists
        $field = new XMLDBField('folderid');
        if (field_exists($table, $field)) {

            $tw=new transaction_wrapper();

            // Drop newsfeed table 'folderid' field
            $result = drop_field($table, $field, false, false);

            $tw->complete($result);
        }

        // Drop newsfeed table startdate field if it exists
        $field = new XMLDBField('startdate');
        if (field_exists($table, $field)) {

            $tw=new transaction_wrapper();

            // Drop newsfeed table 'startdate' field
            $result = drop_field($table, $field, false, false);

            $tw->complete($result);
        }
    }

    if ($result && $oldversion < 2010012700) {
        // Clear newsfeed cache (required as result of tag validation correction)
        $folder=$CFG->dataroot.feed_system::FEED_CACHE_FOLDER;
        if($handle=opendir($folder)) {
            while(($file=readdir($handle))!==false) {
                if(preg_match('/\.atom$/',$file)) {
                    unlink($folder.'/'.$file);
                }
            }
            closedir($handle);
        }
    }

    return $result;
}

?>
