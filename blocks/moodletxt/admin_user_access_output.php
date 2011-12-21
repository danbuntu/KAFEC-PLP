<?php

    /**
     * Output file for user admin panel.
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2006 Onwards, Cy-nap Ltd. All rights reserved.
     * @version 2008082112
     * @since 2007082312
     */

?>
    <script type="text/javascript" src="js/lib.js"></script>
    <script type="text/javascript" src="js/ajaxboot.js"></script>
    <script type="text/javascript" src="js/admin_user_access.js"></script>
    <script type="text/javascript">
    <!--
    //<![CDATA[

        // Declarations from PHP - must stay here, not in library file
        var wwwroot = '<?php echo($CFG->wwwroot); ?>';
        var vkey = '<?php echo($SESSION->moodletxt->vkey); ?>';
        var proc_url = 'admin_user_access_process.php';

    //]]>
    //-->
    </script>
    <div>
        <div id="mdltxt_usertree_loadingPanel">
            <p>
                <img src="pix/ajax-loader.gif" width="16" height="16" alt="Loading" title="Loading..." />
                <?php echo(get_string('loadtoken', 'block_moodletxt')); ?>
            </p>
        </div>
        <!--
            USER ACCESS PANEL
        -->
        <div id="mdltxt_usertree_userAccessPanel">
            <h3 id="useraccessheader"><?php echo(get_string('adminheaderaccess', 'block_moodletxt')); ?></h3>
            <p>
              <?php echo(get_string('adminlabelteachername', 'block_moodletxt')); ?>
              <span id="userFormName"> </span><br />
              <?php echo(get_string('adminlabelteacheruser', 'block_moodletxt')); ?>
             <span id="userFormMoodleID"> </span><br />
              <?php echo(get_string('adminlabelteachercourse', 'block_moodletxt')); ?>
              <span id="userFormCourse"> </span>
            </p>
            <form id="removeaccessform" action="<?php echo($ME); ?>" method="post" onsubmit="return submitRemAccess();">
                <input type="hidden" name="formid" value="removeaccess" />
                <input type="hidden" id="removeaccessuserid" name="userID" value="" />
                <input type="hidden" id="removecourseid" name="courseID" value="" />
                <label for="accountaccess">
                    <?php echo(get_string('adminlabelaccess', 'block_moodletxt')); ?><br />
                    <select id="useraccountaccess" name="accountaccess" size="3" style="width:100%;" disabled="disabled" onchange="javascript:activateRemoveControls(this);">
                    </select>
                </label>
                <span style="float:right;">
                    <input id="removeaccessbutton" type="submit" value="<?php echo(get_string('adminbutdeleteaccess', 'block_moodletxt')); ?>" disabled="disabled" />
                </span>
            </form>
            <br /><br />
            <form id="grantaccessform" action="<?php echo($ME); ?>" method="post" onsubmit="return submitGrantAccess();">
                <input type="hidden" name="formid" value="grantaccess" />
                <input type="hidden" id="grantaccessuserid" name="userID" value="" />
                 <input type="hidden" id="grantcourseid" name="courseID" value="" />
                <label for="usertxttoolsaccounts">
                    <?php echo(get_string('adminlabelexistingacc', 'block_moodletxt')); ?><br />
                    <select id="usertxttoolsaccounts" name="txttoolsaccounts" size="3" style="width:100%;" disabled="disabled" onchange="javascript:activateGrantControls(this);">
                        <?php echo($accountlist); ?>
                    </select>
                </label>
                <span style="float:right;clear:both;">
                    <input id="grantaccessbutton" type="submit" value="<?php echo(get_string('adminbutgrantaccess', 'block_moodletxt')); ?>" disabled="disabled" />
                </span>
            </form>
            <br /><br />
            <form id="userfilterformdel" action="<?php echo($ME); ?>" method="post" onsubmit="return submitRemFilter();">
                <input type="hidden" name="formid" value="deleteuserfilter" />
                <input type="hidden" id="delfilteruserid" name="delfilteruserid" value="" />
                <input type="hidden" id="delfiltercourseid" name="delfiltercourseid" value="" />
                <label for="userinboundfilters">
                    <?php echo(get_string('adminlabeluserfilters', 'block_moodletxt')); ?><br />
                    <select id="userinboundfilters" name="userinboundfilters" size="3" style="width:100%;" disabled="disabled" onchange="javascript:actUserFilterDelControls(this);">
                    </select>
                </label>
                <span style="float:right;clear:both;">
                    <input id="userdelfilterbutton" type="submit" value="<?php echo(get_string('adminbutdeluserfilter', 'block_moodletxt')); ?>" disabled="disabled" />
                </span>
            </form>
            <br /><br />
            <form id="userfilterformadd" action="<?php echo($ME); ?>" method="post" onsubmit="return submitAddFilter();">
                <input type="hidden" name="formid" value="adduserfilter" />
                <input type="hidden" id="addfilteruserid" name="addfilteruserid" value="" />
                <input type="hidden" id="addfiltercourseid" name="addfiltercourseid" value="" />
                <label for="userfilteraccounts">
                    <?php echo(get_string('adminlabelfilteracc', 'block_moodletxt')); ?>
                    <select id="userfilteraccounts" name="userfilteraccounts" size="1" style="width:100%;">
                        <?php echo($accountlist); ?>
                    </select>
                </label>
                <br /><br />
                <label for="userfilterswkeyword">
                    <input type="radio" id="userfilterswkeyword" name="filtertype" value="keyword" checked="checked" />
                    <?php echo(get_string('adminlabelfilterkeyword', 'block_moodletxt')); ?>
                </label>
                <input type="text" id="userfilterkeyword" name="userfilterkeyword" size="15" value="" /><br />
                <label for="userfilterswphone">
                    <input type="radio" id="userfilterswphone" name="filtertype" value="phoneno" />
                    <?php echo(get_string('adminlabelfilterphone', 'block_moodletxt')); ?>
                </label>
                <input type="text" id="userfilterphone" name="userfilterphone" size="15" value="" />
                <span style="float:right;clear:both;">
                    <input id="useraddfilterbutton" type="submit" value="<?php echo(get_string('adminbutadduserfilter', 'block_moodletxt')); ?>" />
                </span>
            </form>
        </div>
        <!--
            COURSE ACCESS PANEL
        -->
        <div id="mdltxt_usertree_courseAccessPanel">
            <h3 id="courseaccessheader"><?php echo(get_string('adminheaderaccess', 'block_moodletxt')); ?></h3>
            <p>
              <?php echo(get_string('adminlabelcoursename', 'block_moodletxt')); ?>
              <span id="courseaccessname"></span>
            </p>
            <form id="courseremoveaccessform" action="<?php echo($ME); ?>" method="post" onsubmit="return submitCourseRem();">
                <input type="hidden" name="formid" value="courseremoveaccess" />
                <input type="hidden" id="courseremovecourseid" name="courseID" value="" />
                <label for="accountaccess">
                    <?php echo(get_string('adminlabelaccesscourse', 'block_moodletxt')); ?><br />
                    <select id="courseaccountaccess" name="accountid" size="3" style="width:100%;" disabled="disabled" onchange="javascript:activateCsRemoveControls(this);">
                    </select>
                </label>
                <span style="float:right;">
                    <input id="removecourseaccessbutton" type="submit" value="<?php echo(get_string('adminbutdelcourseaccess', 'block_moodletxt')); ?>" disabled="disabled" />
                </span>
            </form>
            <br /><br />
            <form id="grantaccessform" action="<?php echo($ME); ?>" method="post" onsubmit="return submitCourseGrant();">
                <input type="hidden" name="formid" value="coursegrantaccess" />
                <input type="hidden" id="coursegrantcourseid" name="courseID" value="" />
                <label for="txttoolsaccounts">
                    <?php echo(get_string('adminlabelexistingacc', 'block_moodletxt')); ?><br />
                    <select id="coursetxttoolsaccounts" name="accountid" size="3" style="width:100%;" disabled="disabled" onchange="javascript:activateCsGrantControls(this);">
                        <?php echo($accountlist); ?>
                    </select>
                </label>
                <span style="float:right;clear:both;">
                    <input id="grantcourseaccessbutton" type="submit" value="<?php echo(get_string('adminbutgrantcourseaccess', 'block_moodletxt')); ?>" disabled="disabled" />
                </span>
            </form>
        </div>
        <ul class="mdltxt_usertree_category">
            <?php echo($categorylist); ?>
        </ul>
    </div>
    <div class="mdltxt_clearer">
    </div>
