<div>
    <table cellpadding=0 cellspacing=0 style="width:100%;">
        <tr>
            <td>
                <ul id="nav" class="dropdown dropdown-horizontal">
                    <?php
                    global $USER, $CFG;
//print the home button
                    $text = '<li id="n-left"><a href="' . $CFG->wwwroot . '/">Home</a></li>';
                    echo $text;

                    $userid = $USER->idnumber;
                    // echo $userid;
                    //print_r($USER->access);
                    //use the following to find the role id and find the named role
                    // select * from mdl_role
                    //check through the access array to see what roles the users belongs to
                    $test = $USER->access['ra']['/1'];
                    if (in_array('18', $USER->access['ra']['/1'])) {

                        $loggedin = $USER->username;


//strip of the _guardian (g) bit
                        $studentname = substr($loggedin, 1);

// get detials for the ward
                        $query = "SELECT * FROM mdl_user WHERE username='" . $studentname . "'";
                        $result = mysql_query($query);
                        while ($row = mysql_fetch_assoc($result)) {
                            $wardid = $row['id'];
                            $firstname = $row['firstname'];
                            $surname = $row['lastname'];
                            echo '<li id="n-left"><a href="' . $CFG->wwwroot . '/blocks/ilp/view.php?courseid=1&id=' . $wardid . '"> PLP for ' . $firstname . ' ' . $surname . '</a></li>';
                        }
                    ?>
                        <!-- print the menu bar for parents -->
                        
                        <li id="n-left"><a href="http://www.midkent.ac.uk/parents-and-carers-zone" class="dir">Parent Zone</a></li>

<!--the following are closing tags for the 'if' section                        -->
            </td>
        </tr>
    </table>
</div>

<?php } elseif (!in_array('18', $USER->access['ra']['/1'])) { ?>


<!-- IMPORTANT don't close the first li tags if there is a ul submenu, this will bust it -->

        <li id="n-right"><a href="<?php echo $CFG->wwwroot; ?>/auth/mnet/jump.php?hostid=3" class="dir">Midkent<i>i</i></a></li>

        <li id="n-right"><a href="<?php echo $CFG->wwwroot; ?>/blocks/ilp/view.php?courseid=1&id=<?php echo  $CFG->id; ?>" class="dir">My PLP</a>
            <ul>
                <li class="first"><a href="<?php echo $CFG->wwwroot; ?>/user/view.php?id=' . $CFG->id . '&course=1">My Profile</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/calendar/view.php?view=upcoming">My Calendar</a></li>
            </ul>

        <li id="n-right"><a href="<?php echo $CFG->wwwroot; ?>/course/category.php?id=36" class="dir">My PT</a></li>

        <li><a href="https://mail.midkent.ac.uk/">My Email</a></li>

        <li class="first"><a href="<?php echo $CFG->wwwroot; ?>/my">My Courses</a>
            <ul>
                <li class="first"><a href="<?php echo $CFG->wwwroot; ?>/course">All Courses</a></li>
            </ul>

        <li id="n-right"><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=36" class="dir">Student Support</a>
            <ul>
                <li><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=1004">Student Handbook</a></li>
                <li><a href="https://sharepoint.midkent.ac.uk/Sites/library2/default.aspx">Learning Resource Centre</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=838">Counselling Service</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=769">Careers</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=773">Equality and Diversity</a></li>
                <li><a href="http://mypc.midkent.ac.uk/cire">Book a PC</a></li>
                <li class="first"><a href="http://www.direct.gov.uk/en/EducationAndLearning/UniversityAndHigherEducation/index.htm">Aim Higher</a></li>
                <li><a href="http://www.connexions.gov.uk/index.cfm?CategoryID=11">Connexions</a></li>
            </ul>

        <li id="n-right"><a href="<?php echo $CFG->wwwroot; ?>" class="dir">Additional links</a>
            <ul>
                <li><a href="<?php echo $CFG->wwwroot; ?>/mod/resource/view.php?id=28197">Help and FAQS</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/mod/resource/view.php?id=24875">Student Attendance Guide</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/mod/resource/view.php?id=24876">Computer Usage Guide</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/mod/resource/view.php?id=24874">College Computer Information</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/mod/resource/view.php?id=24873">Data Protection</a></li>
                <li><a href="https://profiler.midkent.ac.uk/Profiler4/">Skills 4 Life Profiler</a></li>
                <li><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=759&cal_m=9&cal_y=2011">School Partnerships</a></li>
            </ul>

        <li id="n-right"><a href="<?php echo $CFG->wwwroot; ?>/course/view.php?id=767" class="dir">Teachers' Tools Kit</a></li>

        <li id="n-right"><?php echo '<a href="http://vshare.midkent.ac.uk" class="dir">.</a>' ?></li>

<!--The closig tags below are for the 'elseif' section                        -->
            </td>
        </tr>
    </table>
</div>

<?php } ?>
