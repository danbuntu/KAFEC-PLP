<?php

    /**
     * Send-message page output file
     *
     * @author Greg J Preece <support@txttools.co.uk>
     * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
     * @version 2011030101
     * @since 2007082312
     */

    $languageStrings = array(
        'errorlabel',
        'errornofirstname',
        'errornolastname',
        'errornomessage',
        'errornonumber',
        'errornorecipientsselected',
        'warnunicode'
    );

?>
    <?php if (moodletxt_get_setting('jQuery_Include_Enabled') == 1) { ?><script type="text/javascript" src="js/jquery.js"></script><?php } ?>
    <script type="text/javascript" src="js/lib.js"></script>
    <script type="text/javascript" src="js/jquery.json.min.js"></script>
    <script type="text/javascript" src="js/jquery.timers.js"></script>
    <script type="text/javascript" src="js/jquery.colour.js"></script>
    <script type="text/javascript" src="js/jquery.selectboxes.js"></script>
    <script type="text/javascript" src="js/sendmessage.js"></script>
    <script type="text/javascript">
    <!--
    //<![CDATA[

        /*
          ############################################################
          # Javascript declarations that originate from PHP -
          # must stay in this file!
          ############################################################
        */

        var userSignature = <?php echo("'" . addslashes($userSigString) . "'"); ?>;

        var userTemplates = new Array(<?php echo(count($userTemplates) + 1); ?>);
            userTemplates[0] = '';
            <?php echo($JStemplateList); ?>

        var accountDescriptions = new Array(<?php echo(count($txttoolsAccounts) + 1); ?>);
            accountDescriptions[0] = '';
            <?php echo($JSaccountList); ?>

        var language = new Array(<?php echo(count($languageStrings)); ?>);
        <?php for($x = 0; $x < count($languageStrings); $x++) echo ("
            language['" . $languageStrings[$x] . "'] = '" . addslashes(get_string($languageStrings[$x], 'block_moodletxt')) . "';"
        ); ?>
            
    //]]>
    -->
    </script>
<?php

    if (count($xmlerrors) > 0) {

?>
    <div class="mdltxt_half_centred" style="color:#FF0000;">
        <p>
            <?php echo(get_string('adminerrorpara1', 'block_moodletxt')); ?>
        </p>
        <?php echo(moodletxt_vomit_errors(array('401', '403', '404', '500', '503', '601'), $xmlerrors)); ?>
        <p>
            <?php echo(get_string('adminerrorpara2', 'block_moodletxt')); ?>
        </p>
    </div>
<?php

    }

    if(count($errorArray) > 0)  {

        echo('<p class="error" style="text-align:center;">' . get_string('sendlabelerrorsfound', 'block_moodletxt') . '</p>');

    }
    
?>
        <div id="navWrapper">
            <ul id="navigator">
                <li id="nav1" class="navigatorControl"><em><?php echo(get_string('sendnavmenu1', 'block_moodletxt')); ?></em></li>
                <li id="nav2" class="navigatorControl"><em><?php echo(get_string('sendnavmenu2', 'block_moodletxt')); ?></em></li>
                <li id="nav3" class="navigatorControl"><em><?php echo(get_string('sendnavmenu3', 'block_moodletxt')); ?></em></li>
                <li id="nav4" class="navigatorControl"><em><?php echo(get_string('sendnavmenu4', 'block_moodletxt')); ?></em></li>
            </ul>
        </div>
        <div class="mdltxt_clearer"></div>
        <form id="messageForm" action="<?php echo($ME); ?>" method="post">
            <input type="hidden" name="courseid" value="<?php echo($courseid); ?>" />
            <div id="slideWrapper">
                <!--
                    SLIDE 1: RECIPIENTS
                -->
                <div id="slidePlate">
                <div id="slide1" class="slide">
                    <h2><?php echo(get_string('sendheaderslide1', 'block_moodletxt')); ?></h2>
                    <?php echo(moodletxt_vomit_errors(array('norecipients', 'noValidNumbers'), $errorArray)); ?><br />
                    <div id="leftPane">
                        <ul id="tabs-nav">
                            <li><?php echo(get_string('sendtabs1', 'block_moodletxt')); ?></li>
                            <li><?php echo(get_string('sendtabs2', 'block_moodletxt')); ?></li>
                            <li><?php echo(get_string('sendtabs3', 'block_moodletxt')); ?></li>
                        </ul>
                        <div id="tab-1" class="sendtab">
                            <label for="userGroupList"><?php echo(get_string('sendlabelusergroups', 'block_moodletxt')); ?></label>
                            <select id="userGroupList" class="sourceBox" size="4" multiple="multiple">    
                                <?php echo($userGroupList); ?>
                            </select>
                            <br /><br />
                            <label for="userList"><?php echo(get_string('sendlabeluserlist', 'block_moodletxt')); ?></label>
                            <select id="userList" class="sourceBox" size="10" multiple="multiple">
                                <?php echo($userList); ?>
                            </select>
                        </div>
                        <div id="tab-2" class="sendtab">
                            <label for="abGroupList"><?php echo(get_string('sendlabeladdressgroups', 'block_moodletxt')); ?></label>
                            <select id="abGroupList" class="sourceBox" size="4" multiple="multiple">    
                                <?php echo($abGroupList); ?>
                            </select>
                            <br /><br />
                            <label for="abList"><?php echo(get_string('sendlabeladdresscontacts', 'block_moodletxt')); ?></label>
                            <select id="abList" class="sourceBox" size="10" multiple="multiple">
                                <?php echo($abList); ?>
                            </select>
                        </div>
                        <div id="tab-3" class="sendtab">
                            <p><?php echo(get_string('sendlabeladditionalname', 'block_moodletxt')); ?></p>
                            <input id="addfirstname" class="sourceBox" type="text" size="15" maxlength="30" name="addfirstname" />
                            <input id="addlastname" class="sourceBox" type="text" size="15" maxlength="30" name="addlastname" /><br />
                            <p><?php echo(get_string('sendlabeladditional', 'block_moodletxt')); ?></p>
                            <input id="addnumber" class="sourceBox" type="text" size="20" maxlength="20" name="addnumber" />
                        </div>
                    </div>
                    <div id="centrePane">
                        <input id="select_multiple" class="submit" type="button" value="&gt;&gt; <?php echo(get_string('sendbutadd', 'block_moodletxt')); ?> &gt;&gt;" />
                        <br /><br /><br />
                        <input id="deselect_multiple" class="submit" type="button" value="&lt;&lt; <?php echo(get_string('sendbutrem', 'block_moodletxt')); ?> &lt;&lt;" />
                        <br /><br /><br />
                        <?php echo(get_string('sendlabelcolourkey', 'block_moodletxt')); ?><br />
                        <select id="colourkey" size="5">
                            <option value="" class="mdltxt_opt_user"><?php echo(get_string('sendkeyuser', 'block_moodletxt')); ?></option>
                            <option value="" class="mdltxt_opt_userGroup"><?php echo(get_string('sendkeyusergroup', 'block_moodletxt')); ?></option>
                            <option value="" class="mdltxt_opt_abContact"><?php echo(get_string('sendkeyab', 'block_moodletxt')); ?></option>
                            <option value="" class="mdltxt_opt_abGroup"><?php echo(get_string('sendkeyabgroup', 'block_moodletxt')); ?></option>
                            <option value="" class="mdltxt_opt_add"><?php echo(get_string('sendkeyadd', 'block_moodletxt')); ?></option>
                        </select>
                    </div>
                    <div id="rightPane">
                        <h3><?php echo(get_string('sendheaderrecipients', 'block_moodletxt')); ?></h3>
                        <select id="finalRecipients" name="finalRecipients[]" size="18" multiple="multiple">
                            <?php echo($selectedRecipients); ?>
                        </select>
                    </div>
                    <div class="mdltxt_clearer"></div>
                    <p class="prevNext">
                        <span class="nextButton"><?php echo(get_string('sendfragnext', 'block_moodletxt')); ?> &gt;&gt;</span>
                    </p>
                </div>
                <!--
                    SLIDE 2: MESSAGE
                -->
                <div id="slide2" class="slide">
                    <h2><?php echo(get_string('sendheaderslide2', 'block_moodletxt')); ?></h2>
                    <?php echo(moodletxt_vomit_errors(array('noMessage'), $errorArray)); ?><br />
                    <select name="messageTemplates" id="messageTemplates" size="1" style="width:45%;">
                        <option value="0" selected="selected"><?php echo(get_string('sendlabelselecttemplate', 'block_moodletxt')); ?></option>
                        <?php echo($templateList); ?>
                    </select><br />
                    <?php echo(get_string('sendlabeladdsig', 'block_moodletxt')); ?>
                    <input type="checkbox" name="addSig" id="addSig" value="yes" />
                    <?php echo(get_string('sendlabelcharsused', 'block_moodletxt')); ?>
                    <input type="text" id="charsUsed" name="notimportant" size="4" />
                    Characters per message: <span id="charactersPerMessage"></span><br />
                    <textarea id="messageText" name="messageText" rows="10" cols="70"><?php echo($inMessage); ?></textarea><br />
                    <p>
                        <?php echo(get_string('sendlabelnametags', 'block_moodletxt')); ?>
                    </p>
                    <input id="tagFirstName" type="button" value="<?php echo(get_string('sendbuttag1', 'block_moodletxt')); ?>" />
                    <input id="tagLastName" type="button" value="<?php echo(get_string('sendbuttag2', 'block_moodletxt')); ?>" />
                    <input id="tagFullName" type="button" value="<?php echo(get_string('sendbuttag3', 'block_moodletxt')); ?>" />
                    <br />
                    <p id="unicodeMessage" style="margin-top:2em;"></p>
                    <p class="prevNext">
                        <span class="nextButton"><?php echo(get_string('sendfragnext', 'block_moodletxt')); ?> &gt;&gt;</span>
                        <span class="prevButton">&lt;&lt; <?php echo(get_string('sendfragprev', 'block_moodletxt')); ?></span>
                    </p>
                </div>
                <!--
                    SLIDE 3: MESSAGE OPTIONS
                -->
                <div id="slide3" class="slide">
                    <h2><?php echo(get_string('sendheaderslide3', 'block_moodletxt')); ?></h2>
                    <fieldset>
                        <legend><?php echo(get_string('sendlegendunicode', 'block_moodletxt')); ?></legend>
                        <?php $checkedString = (! isset($inSuppressUnicode) || $inSuppressUnicode != 1) ? ' checked="checked"' : ''; ?>
                        <label for="suppressUnicodeNo">
                            <input type="radio" name="suppressUnicode" id="suppressUnicodeNo" value="0"<?php echo($checkedString); ?> />
                            <?php echo(get_string('sendlabelsuppressno', 'block_moodletxt')); ?>
                        </label><br /><br />
                        <?php $checkedString = (isset($inSuppressUnicode) && $inSuppressUnicode == 1) ? ' checked="checked"' : ''; ?>
                        <label for="suppressUnicodeYes">
                            <input type="radio" name="suppressUnicode" id="suppressUnicodeYes" value="1"<?php echo($checkedString); ?> />
                            <?php echo(get_string('sendlabelsuppressyes', 'block_moodletxt')); ?>
                        </label>
                    </fieldset>
                    <br />
                    <fieldset>
                        <legend><?php echo(get_string('sendlegendschedule', 'block_moodletxt')); ?></legend>
                        <?php echo(moodletxt_vomit_errors(array('noSchedule', 'invalidDate', 'pastDate'), $errorArray)); ?><br />
                        <?php $checkedString = ((! isset($inSchedule)) || ($inSchedule != 'schedule')) ? ' checked="checked"' : ''; ?>
                        <label for="schedule1">
                            <input type="radio" name="schedule" id="schedule1" value="now"<?php echo($checkedString); ?> />
                            <?php echo(get_string('sendlabelsendnow', 'block_moodletxt')); ?>
                        </label><br />
                        <?php $checkedString = ((isset($inSchedule)) && ($inSchedule == 'schedule')) ? ' checked="checked"' : ''; ?>
                        <label for="schedule2">
                            <input type="radio" name="schedule" id="schedule2" value="schedule"<?php echo($checkedString); ?> />
                            <?php echo(get_string('sendlabelschedule', 'block_moodletxt')); ?>
                        </label>
                        <?php print_date_selector('schedule_day', 'schedule_month', 'schedule_year', $scheduleTimestamp); echo(' ' .get_string('sendlabelschedulefrag', 'block_moodletxt') . ' '); print_time_selector('schedule_hour', 'schedule_minute', $scheduleTimestamp); ?>
                    </fieldset>
                    <br/>
                    <fieldset>
                        <legend><?php echo(get_string('sendlegendaccounts', 'block_moodletxt')); ?></legend>
                        <?php echo(moodletxt_vomit_errors(array('nolink'), $errorArray)); ?>
                        <p>
                          <?php echo(get_string('sendmultipleaccounts', 'block_moodletxt')); ?>
                        </p>
                        <label for="txttoolsaccount">
                            <?php echo(get_string('sendlabelaccount', 'block_moodletxt')); ?>
                            <select id="txttoolsaccount" name="txttoolsaccount" size="1">
                                <?php echo($accountList); ?>
                            </select>
                        </label><br /><br />
                        <label for="accountDescription">
                            <?php echo(get_string('sendlabelaccountdesc', 'block_moodletxt')); ?>
                            <span id="accountDescription"></span>
                        </label>
                    </fieldset>
                    <p class="prevNext">
                        <span class="nextButton"><?php echo(get_string('sendfragnext', 'block_moodletxt')); ?> &gt;&gt;</span>
                        <span class="prevButton">&lt;&lt; <?php echo(get_string('sendfragprev', 'block_moodletxt')); ?></span>
                    </p>
                </div>
                <!--
                    SLIDE 4: REVIEW AND SEND
                -->
                <div id="slide4" class="slide">
                    <h2><?php echo(get_string('sendheaderslide4', 'block_moodletxt')); ?></h2>
                    <?php echo(get_string('sendconfirmrecipients', 'block_moodletxt')); ?><br />
                    <select id="confirmRecipients" name="confirmRecipients" size="5" multiple="multiple" style="width:50%;">
                            <?php echo($selectedRecipients); ?>
                    </select><br />
                    <?php echo(get_string('sendconfirmmessage', 'block_moodletxt')) ?> 
                    (<span id="confirmCharsUsed"></span> <?php echo(get_string('sendconfirmcharacters', 'block_moodletxt')); ?>/<span id="confirmMessagesUsed"></span> <?php echo(get_string('sendconfirmsms', 'block_moodletxt')); ?>)<br />
                    <textarea id="confirmMessage" rows="10" cols="70" disabled="disabled"></textarea><br />
                    <p>
                        <?php echo(get_string('sendconfirmschedule', 'block_moodletxt')); ?> <span id="confirmSchedule"></span><br />
                    </p>
                    <p>
                        <input id="sendMessage" class="submit" type="submit" value="Send Message" />
                    </p>
                    <div class="mdltxt_clearer"></div>
                    <p class="prevNext">
                        <span class="prevButton">&lt;&lt; <?php echo(get_string('sendfragprev', 'block_moodletxt')); ?></span>
                    </p>
                </div>
                </div>
            </div>
        </form>
        
        <script type="text/javascript">
            <!--
            //<![CDATA[

            $(document).ready(function() {
<?php

    /*
     * Go through $slideErrors array and
     * highlight any slides with errors
     */
    $firstErrorSlide = 0;
    for($x = 4; $x > 0; $x--) {

        if (isset($slideErrors[$x]) && $slideErrors[$x] === true) {
            $firstErrorSlide = $x;
?>
                glowSlides[<?php echo($x); ?>] = <?php echo($x); ?>;
<?php

        }

    }

?>
                var firstErrorSlide = <?php echo($firstErrorSlide); ?>;

                // Just use the navigator click() events - does everything for you
                if (firstErrorSlide > 0) {
                    $('ul#navigator li:nth-child(' + firstErrorSlide + ')').click();
                } else {
                    $('ul#navigator li:nth-child(1)').click();
                }



            });
            //]]>
            -->
        </script>