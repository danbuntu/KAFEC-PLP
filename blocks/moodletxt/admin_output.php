<?php

    /**
     * Output file for admin page
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030101
     * @since 2007082312
     */

?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/jquery.selectboxes.js"></script>
    <script type="text/javascript" src="js/jquery.json.min.js"></script>
    <script type="text/javascript" src="js/jquery.bgiframe.min.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.min.js"></script>
    <script type="text/javascript" src="js/lib.js"></script>
    <script type="text/javascript" src="js/admin.js"></script>
    <script type="text/javascript">
    <!--
    //<![CDATA[

        // Declarations from PHP - must stay here, not in library file
        var loadingNotice = '<?php echo(get_string('loadtoken', 'block_moodletxt')); ?>';
        var searchUserString = '<?php echo(get_string('adminlabelfilterusersearch', 'block_moodletxt')); ?>';

        var inboxarr = new Array();
        <?php echo($jsarray); ?>

    //]]>
    //-->
    </script>
    <div class="mdltxt_left">
        <a href="userstats.php"><?php echo(get_string('adminlinkuserstats', 'block_moodletxt')); ?></a>
    </div>
    <div class="clearer">
    </div>
    <?php echo($connErrorString); ?>
    <?php echo($SSLErrorString); ?>
    <?php echo($RSSstring); ?>

    <!--
        TXTTOOLS ACCOUNTS FORM
    -->
    <div class="mdltxt_left">
        <h2><?php echo(get_string('adminheaderaccounts', 'block_moodletxt')); ?></h2>
        <form id="formtxttoolsaccounts" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="accounts" />
            <fieldset>
                <legend><?php echo(get_string('adminlabelexistingacc', 'block_moodletxt')); ?></legend>
                <a href="admin_accounts.php"><?php echo(get_string('adminlinkaccounts', 'block_moodletxt')); ?></a><br />
                <?php echo(moodletxt_vomit_errors(array('401', '403', '404', '500', '503', '601', 'invalidaccount', 'addNotInserted'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('addInserted'), $noticeArray, true)); ?>
                <label for="edittxttoolsaccounts">
                    <select id="edittxttoolsaccounts" name="account" size="5" style="width:100%;">
                        <?php echo($accountListString); ?>
                    </select>
                </label>
                <?php echo(moodletxt_vomit_errors(array('nopassword'), $errorArray)); ?>
                <label for="updatepasswordbox">
                    <?php echo(get_string('adminlabelchangepass', 'block_moodletxt')); ?>
                    <input id="updatepasswordbox" type="password" name="newpassword" size="20"<?php echo($disableString); ?> />
                </label><br />
                <label for="updatedefaultinbox">
                    <?php echo(get_string('adminlabelaccinbox', 'block_moodletxt')); ?>
                    <select name="defaultinbox" id="updatedefaultinbox" size="1" disabled="disabled">
                        <?php echo($defaultinboxlist); ?>
                    </select>
                </label><br />
                <span style="float:right;">
                    <input id="updatepasswordbutton" type="submit" name="txttoolsubmit" value="<?php echo(get_string('adminbutupdatepass', 'block_moodletxt')); ?>" disabled="disabled" />
                </span>
            </fieldset>
        </form>
        <br />
        <form id="formaddaccount" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="addaccount" />
            <fieldset>
                <legend><?php echo(get_string('adminlabeladdaccount', 'block_moodletxt')); ?></legend>
                <?php echo(moodletxt_vomit_errors(array('addNoAccountName', 'addNoPassword', 'addNoMatch', 'addExists'), $errorArray)); ?>
                <label for="addaccountname" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccusername', 'block_moodletxt')); ?>
                </label>
                <input type="text" id="addaccountname" name="accountname" size="20" maxlength="20" value="<?php echo($addAccountName); ?>" /><br />
                <label for="addaccountpass" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccpassword', 'block_moodletxt')); ?>
                </label>
                <input id="addaccountpass" type="password" name="password1" size="20" maxlength="20" /><br />
                <label for="addaccountpass2" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccpassword2', 'block_moodletxt')); ?>
                </label>
                <input id="addaccountpass2" type="password" name="password2" size="20" maxlength="20" /><br />
                <label for="addaccountdesc" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccdesc', 'block_moodletxt')); ?>
                </label>
                <input id="addaccountdesc" type="text" name="accountdescription" size="20" maxlength="255" value="<?php echo(stripslashes($addDescription)); ?>" /><br />
                <label for="addaccountinbox" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelaccinbox', 'block_moodletxt')); ?>
                </label>
                <select id="addaccountinbox" name="defaultinbox" size="1">
                    <?php echo($defaultinboxlist2); ?>
                </select><br /><br />
                <span style="float:right;">
                    <input type="submit" value="<?php echo(get_string('adminbutaddaccount', 'block_moodletxt')); ?>" />
                </span>
            </fieldset>
        </form>
    </div>
    <div class="mdltxt_right">
        <h2><?php echo(get_string('adminheaderfilters', 'block_moodletxt')); ?></h2>
        <form id="forminboundfilters" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="editfilters" />
            <fieldset>
                <?php echo(moodletxt_vomit_errors(array('filterNoAccount'), $errorArray)); ?>
                <?php echo(moodletxt_vomit_errors(array('filterUpdated', 'filterDeleted'), $noticeArray, true)); ?>
                <label for="filterAccountList" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelexistingacc', 'block_moodletxt')); ?>
                </label>
                <select id="filterAccountList" name="filterAccountList" size="1" style="min-width:30%;">
                    <option value=""><?php echo(get_string('adminlabelselectacc', 'block_moodletxt')); ?></option>
                    <?php echo($filterAccountListString); ?>
                </select>
                <br /><br />
                <span id="loadingFilters" class="mdltxt_aligned_form_field"> </span>
                <br />
                <?php echo(moodletxt_vomit_errors(array('noFilterSelected', 'filterExists', 'invalidNewPhoneFilter'), $errorArray)); ?>
                <div id="existingKeywordFilterDiv">
                    <label for="existingKeywordFilterList" class="mdltxt_align_form">
                        <?php echo(get_string('adminlabelfilterskeyword', 'block_moodletxt')); ?>:
                    </label>
                    <select id="existingKeywordFilterList" name="existingKeywordFilterList" size="1" style="min-width:30%;" disabled="disabled">
                        <option value=""></option>
                    </select>
                    <a id="createNewKeywordFilter" href="#"><?php echo(get_string('adminlabelcreatenew', 'block_moodletxt')); ?></a>
                </div>
                <div id="newKeywordFilterDiv" style="display:none;">
                    <label for="newKeywordFilter" class="mdltxt_align_form">
                        <?php echo(get_string('adminlabelfilterkeyword', 'block_moodletxt')); ?>:
                    </label>
                    <input type="text" id="newKeywordFilter" name="newKeywordFilter" style="min-width:30%;" disabled="disabled" />
                    <a id="cancelNewKeywordFilter" href="#"><?php echo(get_string('cancel', 'block_moodletxt')); ?></a>
                </div>
                <div id="existingPhoneNumberFilterDiv">
                    <label for="existingPhoneNumberFilterList" class="mdltxt_align_form">
                        <?php echo(get_string('adminlabelfiltersphone', 'block_moodletxt')); ?>:
                    </label>
                    <select id="existingPhoneNumberFilterList" name="existingPhoneNumberFilterList" size="1" style="min-width:30%;" disabled="disabled">
                        <option value=""></option>
                    </select>
                    <a id="createNewPhoneNumberFilter" href="#"><?php echo(get_string('adminlabelcreatenew', 'block_moodletxt')); ?></a>
                </div>
                <div id="newPhoneNumberFilterDiv" style="display:none;">
                    <label for="newPhoneNumberFilter" class="mdltxt_align_form">
                        <?php echo(get_string('adminlabelfilterphone', 'block_moodletxt')); ?>
                    </label>
                    <input type="text" id="newPhoneNumberFilter" name="newPhoneNumberFilter" style="min-width:30%;" disabled="disabled" />
                    <a id="cancelNewPhoneNumberFilter" href="#"><?php echo(get_string('cancel', 'block_moodletxt')); ?></a>
                </div>
                <br />
                    <span id="loadingUsersOnFilter" class="mdltxt_aligned_form_field"> </span>
                <br />
                <?php echo(moodletxt_vomit_errors(array('noUsersSelected'), $errorArray)); ?>
                <select id="usersOnFilter" name="usersOnFilter[]" size="5" multiple="multiple" class="mdltxt_aligned_form_field" style="min-width:30%;" disabled="disabled">
                    <option value=""></option>
                </select><br />
                <label for="textSearcher" class="mdltxt_align_form">
                    <?php echo(get_string('adminlabelfilteraddusers', 'block_moodletxt')); ?>:
                </label>
                <input type="text" id="textSearcher" name="textSearcher" value="<?php echo(get_string('adminlabelfilterusersearch', 'block_moodletxt')); ?>" style="min-width:30%;" disabled="disabled" /><br />
                <button id="removeUsersFromFilter" class="mdltxt_aligned_form_field" disabled="disabled"><?php echo(get_string('adminbutremovefilterusers', 'block_moodletxt')); ?></button>
                <br /><br />
                <button type="submit" id="saveFilterButton" class="mdltxt_aligned_form_field" disabled="disabled"><?php echo(get_string('adminbutsavefilterusers', 'block_moodletxt')); ?></button>
            </fieldset>
        </form>
    </div>
    <div class="clearer" style="margin-bottom:2em;"></div>

    <!--
        USEFUL INFORMATION
    -->
    <div class="mdltxt_left">
        <h2><?php echo(get_string('adminheaderinfo', 'block_moodletxt')); ?></h2>
        <fieldset>
            <legend><?php echo(get_string('admininfoheader1', 'block_moodletxt')); ?></legend>
            <p>
              <?php echo(get_string('admininfopara1', 'block_moodletxt')); ?>
            </p>
            <p>
              <?php echo(get_string('admininfopara2', 'block_moodletxt')); ?>
            </p>
            <input type="text" name="" value="<?php echo($sslpushpath); ?>"  style="width:100%;" /><br />
            <p>
              <?php echo(get_string('admininfopara3', 'block_moodletxt')); ?>
            </p>
            <input type="text" name="" value="<?php echo($pushpath); ?>"  style="width:100%;" /><br /><br />
        </fieldset>
        <fieldset style="margin-top:2em;    ">
            <legend><?php echo(get_string('admininfoheader2', 'block_moodletxt')); ?></legend>
            <p>
              <?php echo(get_string('admininfopara5', 'block_moodletxt')); ?>
            </p>
            <p>
              <?php echo(get_string('admininfocontacttel', 'block_moodletxt')); ?>  +44 (0) 113 234 2111<br />
              <?php echo(get_string('admininfocontactfax', 'block_moodletxt')); ?> +44 (0) 113 243 9289<br />
              <?php echo(get_string('admininfocontactemail', 'block_moodletxt')); ?> <a href="mailto:support@txttools.co.uk">support@txttools.co.uk</a><br /><br />
              <a href="http://www.txttools.co.uk"><?php echo(get_string('admininfocontactweb', 'block_moodletxt')); ?></a>
            </p>
        </fieldset>
    </div>

    <!--
        SYSTEM SETTINGS FORM
    -->
   <div class="mdltxt_right">
        <h2><?php echo(get_string('adminheadersettings', 'block_moodletxt')); ?></h2>
        <form id="formsettings" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="formid" value="settings" />
            <?php echo(moodletxt_vomit_errors(array('settingupdates'), $noticeArray, true)); ?>
            <?php echo(moodletxt_vomit_errors(array('settingupdatesfailed', 'settinginvalidprefix'), $errorArray)); ?>
            <?php $statusstring = ($settings['Get_Status_On_View']->value == 1) ? ' checked="checked"' : ''; ?>
            <?php $warnstring = ($settings['Protocol_Warnings_On']->value == 0) ? ' checked="checked"' : ''; ?>
            <?php $inboundstring = ($settings['Get_Inbound_On_View']->value == 1) ? ' checked="checked"' : ''; ?>
            <?php $showinboundstring = ($settings['Show_Inbound_Numbers']->value == 1) ? ' checked="checked"' : ''; ?>
            <?php $jqueryincludestring = ($settings['jQuery_Include_Enabled']->value == 1) ? ' checked="checked"' : '' ?>
            <?php $jqueryuiincludestring = ($settings['jQuery_UI_Include_Enabled']->value == 1) ? ' checked="checked"' : '' ?>
            <label for="jQueryIncludeEnabled">
                <input id="jQueryIncludeEnabled" type="checkbox" name="jQuery_Include_Enabled" value="1"<?php echo($jqueryincludestring); ?> />
                <?php echo(get_string('adminlabeljqueryinclude', 'block_moodletxt')); ?>
            </label><br /><br />
            <label for="jQueryUIIncludeEnabled">
                <input id="jQueryUIIncludeEnabled" type="checkbox" name="jQuery_UI_Include_Enabled" value="1"<?php echo($jqueryuiincludestring); ?> />
                <?php echo(get_string('adminlabeljqueryuiinclude', 'block_moodletxt')); ?>
            </label><br /><br />
            <label for="autogetstatus">
                <input id="autogetstatus" type="checkbox" name="Get_Status_On_View" value="1"<?php echo($statusstring); ?> />
                <?php echo(get_string('adminlabelsetautoupdate', 'block_moodletxt')); ?>
            </label><br /><br />
            <label for="autogetinbound">
                <input id="autogetinbound" type="checkbox" name="Get_Inbound_On_View" value="1"<?php echo($inboundstring); ?> />
                <?php echo(get_string('adminlabelsetautoinbound', 'block_moodletxt')); ?>
            </label><br /><br />
            <label for="showInboundNumbers">
                <input id="showInboundNumbers" type="checkbox" name="Show_Inbound_Numbers" value="1"<?php echo($showinboundstring); ?> />
                <?php echo(get_string('adminlabelshowinbound', 'block_moodletxt')); ?>
            </label><br /><br />
            <label for="pushusername">
                <?php echo(get_string('adminlabelsetxmluser', 'block_moodletxt')); ?>
                <input id="pushusername" type="text" size="20" name="Push_Username" value="<?php echo($settings['Push_Username']->value); ?>" />
            </label><br /><br />
            <label for="pushpassword">
                <?php echo(get_string('adminlabelsetxmlpass', 'block_moodletxt')); ?>
                <input id="pushpassword" type="password" size="20" name="Push_Password" value="" />
            </label><br /><br />
            <label for="connprotocol">
                <?php echo(get_string('adminlabelprotocol', 'block_moodletxt')); ?>
                <select id="connprotocol" name="Use_Protocol" size="1">
                    <option value="SSL"<?php if ($settings['Use_Protocol']->value == 'SSL') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectprotocolssl', 'block_moodletxt')); ?></option>
                    <option value="HTTP"<?php if ($settings['Use_Protocol']->value == 'HTTP') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectprotocolhttp', 'block_moodletxt')); ?></option>
                </select>
            </label><br /><br />
            <label for="disableSSLwarn">
                <input id="disableSSLwarn" type="checkbox" name="Protocol_Warnings_On" value="0"<?php echo($warnstring); ?> />
                <?php echo(get_string('adminlabeldisablewarn', 'block_moodletxt')); ?>
            </label><br /><br />
            <label for="rssupdate">
                <?php echo(get_string('adminlabelrssupdate', 'block_moodletxt')); ?>
                <select id="rssupdate" name="RSS_Update_Interval" size="1">
                    <option value="3600"<?php if ($settings['RSS_Update_Interval']->value == '3600') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrsshourly', 'block_moodletxt')); ?></option>
                    <option value="86400"<?php if ($settings['RSS_Update_Interval']->value == '86400') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrssdaily', 'block_moodletxt')); ?></option>
                    <option value="604800"<?php if ($settings['RSS_Update_Interval']->value == '604800') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrssweekly', 'block_moodletxt')); ?></option>
                    <option value="2419200"<?php if ($settings['RSS_Update_Interval']->value == '2419200') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrssmonthly', 'block_moodletxt')); ?></option>
                </select>
            </label><br /><br />
            <label for="rssexpire">
                <?php echo(get_string('adminlabelrssexpire', 'block_moodletxt')); ?>
                <select id="rssexpire" name="RSS_Expiry_Length" size="1">
                    <option value="86400"<?php if ($settings['RSS_Expiry_Length']->value == '86400') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrssexday', 'block_moodletxt')); ?></option>
                    <option value="604800"<?php if ($settings['RSS_Expiry_Length']->value == '604800') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrssexweek', 'block_moodletxt')); ?></option>
                    <option value="2419200"<?php if ($settings['RSS_Expiry_Length']->value == '2419200') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectrssexmonth', 'block_moodletxt')); ?></option>
                </select>
            </label><br /><br />
            <label for="nationalPrefix">
                <?php echo(get_string('adminlabeldefnatprefix', 'block_moodletxt')); ?>
                <input id="nationalPrefix" type="text" size="6" name="National_Prefix" value="<?php echo($settings['National_Prefix']->value); ?>" />
            </label><br /><br />
            <label for="defaultInternationalPrefix">
                <?php echo(get_string('adminlabeldefaultprefix', 'block_moodletxt')); ?>
                <input id="defaultInternationalPrefix" type="text" size="6" name="Default_International_Prefix" value="<?php echo($settings['Default_International_Prefix']->value); ?>" />
            </label><br /><br />
            <label for="phonesource">
                <?php echo(get_string('adminlabelphonesource', 'block_moodletxt')); ?>
                <select id="phonesource" name="Phone_Number_Source" size="1">
                    <option value="phone2"<?php if ($settings['Phone_Number_Source']->value == 'phone2') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectphone2', 'block_moodletxt')); ?></option>
                    <option value="phone1"<?php if ($settings['Phone_Number_Source']->value == 'phone1') echo(' selected="selected"'); ?>><?php echo(get_string('adminselectphone1', 'block_moodletxt')); ?></option>
                </select>
            </label><br /><br />
            <label for="defaultName">
                <?php echo(get_string('adminlabeldefaultname', 'block_moodletxt')); ?>
                <input id="defaultName" type="text" size="15" name="Default_Recipient_Name" value="<?php echo($settings['Default_Recipient_Name']->value); ?>" />
            </label><br /><br />
            <label for="proxyAddress">
                <?php echo(get_string('adminlabelproxyaddress', 'block_moodletxt')); ?>
                <input id="proxyAddress" type="text" size="15" name="Proxy_Host" value="<?php echo($settings['Proxy_Host']->value); ?>" />
            </label><br /><br />
            <label for="proxyPort">
                <?php echo(get_string('adminlabelproxyport', 'block_moodletxt')); ?>
                <input id="proxyPort" type="text" size="06" name="Proxy_Port" value="<?php echo($settings['Proxy_Port']->value); ?>" />
            </label><br /><br />
            <label for="proxyUsername">
                <?php echo(get_string('adminlabelproxyusername', 'block_moodletxt')); ?>
                <input id="proxyUsername" type="text" size="15" name="Proxy_Username" value="<?php echo($settings['Proxy_Username']->value); ?>" />
            </label><br /><br />
            <label for="proxyPassword">
                <?php echo(get_string('adminlabelproxypassword', 'block_moodletxt')); ?>
                <input id="proxyPassword" type="password" size="15" name="Proxy_Password" value="<?php echo($settings['Proxy_Password']->value); ?>" />
            </label><br /><br />
            <span style="float:right;">
                <input type="submit" value="<?php echo(get_string('adminbutupdatesettings', 'block_moodletxt')); ?>" />
            </span>
        </form>
    </div>
    <div class="clearer">
    </div>
