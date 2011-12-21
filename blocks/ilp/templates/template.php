<?php
// accordian code from: http://www.lutsr.nl/yui/accordion/
// needs refactoring to work with moodle included yui
//include the section to make the accordion menu work - warning hardcoded


include ('accord.php');
//include ('accord2.php');


include ('accord_functions.php');


include('corero_conection.php');

// grab the student number early on and strip to 8 digits. This is then passed to the queries
$student_number = $user->idnumber;
$student_number = substr($student_number, 0, 8);



echo '<link rel="stylesheet" type="text/css" href="http://moodle.midkent.ac.uk/blocks/ilp/templates/custom/accordcss.css" />';
echo '<link rel="stylesheet" type="text/css" href="http://moodle.midkent.ac.uk/blocks/ilp/templates/custom/print.css" media="print"/>';



// Firebug stuff
//require_once('FirePHPCore/fb.php');
// Part of the tempate core NOT TO BE REMOVED!
include ('access_context.php');

//Get current year
$year = date(Y);

// work out the current academic year
//@FIXME break out into a class?
function greaterDate($start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    if ($start - $end < 0)
        return 1;
    else
        return 0;
}

$tdate = date("d M");
//echo 'date is: ' . $tdate;

$date1 = '31 Jul';
if (greaterDate($date1, $tdate)) {
    //  echo 'yes date';
    $academicyear = date("Y");
    // echo $academicyear;
} else {
    //  echo 'no date';
    $academicyear = date("Y") - 1;
    // echo $academicyear;
}
//end of working out the academic year
//
// Begin layout table
?>

<!-- table to enclose the report and make it go full screen on firefox -->
<table style="width: 100%;"><tr><td>

            <div id="hidden">
                <table width="100%"><tr><td>
                            <img src="http://moodle.midkent.ac.uk/theme/midkent_newstyle/logos/logo_mid_kent_college2.png">


                        </td><td> <h1>PLP for <?php echo fullname($user); ?></h1>   </td></tr></table>


            </div>

            <!-- sits as the top of the page so that info is loaded first to dispaly latter, ie the photo -->
            <div id="flightplan">
<?php
include('flightplan.php');
include('attendance.php');
echo '<br/>';
include('attendence_headline.php'); ?>
                <div id="external_databases">
                <?php


                include('badges.php');
                
                include('learnerdetails.php');
               // include('external_databases.php');
                //include('mssqltest.php');
                // MIS test stuff

                include('qualifications.php');

                include('attendance_live.php');
                include('qca.php');
                 


                accord_first('Other Actions');
                //echo 'This is only a test block<br/>';
                $url = $CFG->wwwroot;
               // echo '<br/>' . $url . '&var1=' . fullname($user) . ' &var2=' . $user->username . '<br/>';
                // send to the medals page and pass through the name, username and id number for use later
                //Check if the user can edit the student info - if they can dispaly the medal edit button, if not hide it
                if (has_capability('block/ilp_student_info:viewclass', $context)) {
                    echo '<form id="medals" action="' . $url . '/badges/selectbadge.php?var1=' . fullname($user) . '&var2=' . $user->username . '&var3=' . $user->id . '&var4=' . $courseid . '" method="post"><input type="submit" value="Edit Medals"></form>';
                }
//                echo '<input type="button" value="Enable/disable guardian account">';
//                echo '<input type="button" value="Reset guardian password">';

                accord_last();



                // echo the button for printing and assign div to hide it when printing ?>
                    <?php
                    echo '<br/>';
                     echo '<br/>';
                      echo '<br/>';
                    echo '<div id="printbutton"><table style="text-align: center;"><td>';
                    echo '<a href="http://s-web1/portal"><img style="border: 0px;" src="http://s-moodledev/blocks/ilp/templates/custom/pix/User-icon.png"   title="Update My Details"/><br/>Update My <br>Student Details</a>';
                    echo '</td><td>';
                    echo '<input type="image" src="http://s-moodledev/theme/midkent_newstyle/tango-icon-theme-0.8.90/32x32/actions/document-print.png"  onClick="window.print()"   value="Print This Page" alt="Print this page"/><br/>Print this page</a>';
                    echo '</td></table></div>';
                    ?>
                </div>
                    
            </div>

            <div id="plp">
                <div class="generalbox" id="ilp-profile-overview">
                    <div id="info">

                        <table class="infotable" style="width: 100%;"><tr><td>
                                    <h1>
<?php
                    echo '<a href="' . $CFG->wwwroot . '/user/view.php?' . (($courseid) ? 'courseid=' . $courseid . '&' : '') . 'id=' . $id . '">';
                    echo '<div id="fullname">' . $learnername . '</div>';
                    echo '</a>';

                    if ($CFG->ilpconcern_status_per_student == 1) {
                        //Orignal line to show students name and status
                        //echo '<span class="main status-' . $studentstatusnum . '" style="margin-left:20px">' . (($access_isuser) ? get_string('mystudentstatus', 'ilpconcern') : get_string('studentstatus', 'ilpconcern')) . ': ' . $thisstudentstatus . '</span>';
                    }
?>
                                    </h1>

                                </td></tr>


                            <tr><td>

                                    <table style="width: 100%;"><tr><td width="80px" rowspan="3">
<?php
                    //comment out original line
                    //print_user_picture($user, (($courseid) ? $courseid : 1), $user->picture, 100);
                    // call the photo include to get the student photo and display it
                    //assigns a class to allow the max width to be set via the css
                    echo '<img class="student_photo" src=//10.0.100.157/' . $studentphoto . '><br/>';
?>


                                            </td></tr><tr><td>
                                                <?php
                                                //set the students number and trim to 8 characters

                                                echo '<b><big<big>Student Number is : </b>' . $student_number . '</big></big><br/>';
                                                echo '<b><big<big>Date of birth is: </b>' . $dob . '</big></big><br/>'; ;
                                                //echo get_string('email') . ': ' . $user->email . '<br />';
                                                //echo get_string('address') . ': ' . $user->address . '<br />';
                                                //echo get_string('phone') . ': ' . $user->phone1 . '<br />';
                                                ?>
                                            </td>

                                            <td style="vertical-align: top; text-align: center;" >

                                                <!-- set the traffic light based on the students status -->
                                                <?php
                                                echo '<big>Student Status</big><br/>';

                                                if ($thisstudentstatus == 'Green') {
                                                    echo '<img src="./pix/light01.png"/>';
                                                } elseif ($thisstudentstatus == 'Amber') {
                                                    echo '<img src="./pix/light03.png"/>';
                                                } else {
                                                    echo '<img src="./pix/light02.png"/>';
                                                }
                                                ?>

                                            </td>
                                        </tr></table>


                                </td></tr></table>
                    </div>


                    <!-- end general box -->
                </div>



                <div id="main_boxes">

<?php
                                                accord_first('Student Info');

                                                if ($config->ilp_show_student_info == 1) {
                                                    echo '<div class="generalbox" id="ilp-student_info-overview">';
                                                    display_ilp_student_info($id, $courseid);
                                                    echo '</div>';
                                                }

                                                accord_last();

                                                accord_first('Student Targets');

                                                if ($config->ilp_show_targets == 1) {
                                                    echo '<div class="generalbox" id="ilp-target-overview">';
                                                    display_ilptarget($id, $courseid);
                                                    echo '</div>';
                                                }
                                                accord_last();
//
                                                accord_first("Student's Progress");

                                                if ($config->ilp_show_concerns == 1) {
                                                    $i = 1;
                                                    while ($i <= 4) {
                                                        if (eval('return $CFG->ilpconcern_report' . $i . ';') == 1) {

                                                            echo '<div class="generalbox" id="ilp-concerns-overview">';
                                                            display_ilpconcern($id, $courseid, $i);
                                                            echo '</div>';
                                                        }
                                                        $i++;
                                                    }
                                                }

                                                accord_last();
//
//                                                accord_first('Personal Reports');
//
//                                                if ($config->ilp_show_personal_reports == 1) {
//
//                                                    display_ilp_personal_report($id, $courseid);
//                                                }
//                                                accord_last();

                                                  accord_first('Subject Report');

                                                if ($config->ilp_show_subject_reports == 1) {
                                                    echo '<div class="generalbox" id="ilp-subject_report-overview">';
                                                    display_ilp_subject_report($id, $courseid);
                                                    echo '</div>';
                                                }

                                              accord_last();
?>

                                            </div>

                                        </div>

                                   </td></tr></table> 

<?php
// close the mssql connection
                                                mssql_close($link);
?>