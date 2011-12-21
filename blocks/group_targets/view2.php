<?php
include('top_include.php');
?>
<div class="container">
    <?php topbar('Group Target Setter', $navItems); ?>
</div>

<div class="container-fluid">

    <?php
    include('course_select_dropdown2.php');

    ?>

    <div class="span6">
        <table>
            <tr>
                <th>Select / Deselect all students on this course</th>
            </tr>
            <tr>
                <td style="vertical-align: middle;"><input type="checkbox"
                                                           onclick="toggleChecked(this.checked)">
                </td>
            </tr>
        </table>

    </div>

    <!--    End the drop down-->
</div>
</div>
</div>

    <div>

        <?php $querycoursename = "SELECT fullname FROM {$CFG->prefix}course WHERE id='" . $_SESSION['course_code_session'] . "'";

    //echo $querycoursename;

            $resultcourse = $mysqli->query($querycoursename);

               while ($row = $resultcourse->fetch_object()) {
                $fullname = $row->fullname;
            }
            ?>

       <?php echo '<b><a class="btn success" href="' . $CFG->wwwroot . '/course/view.php?id=' .  $_SESSION['course_code_session'] . '">Curently selected course is ' . $fullname . '</a></b>' ?>
    </div>


<?php
$domain = $_SERVER['HTTP_HOST'];
//select and show all students on this course used roleid 5 to indentify studentsserver
$select = "SELECT  distinct u.id, u.firstname, u.lastname, u.idnumber, u.username";
$from = " FROM {$CFG->prefix}role_assignments a JOIN {$CFG->prefix}user u on a.userid=u.id LEFT JOIN {$CFG->prefix}groups_members gm ON gm.userid=a.userid";
$where = " WHERE contextid='" . $_SESSION['course_context_session'] . "'";
$and = " AND a.roleid='5' order by lastname";
if ($_SESSION['course_group_session'] == 'All groups') {
    $andgroup = " ";
} elseif ($_SESSION['course_group_session'] != 'All groups') {
    $andgroup = " AND gm.groupid='" . $_SESSION['course_group_session'] . "'";
}

$querystudents = $select . $from . $where . $andgroup . $and;

//echo $querystudents;

$resultsstudents = $mysqli->query($querystudents);
$num_students = $resultsstudents->num_rows;
//echo 'num rows: ', $num_students;
$count = 0;

?>

<?php
// Reset all graph score to zero
include('zero-scores.php');

// Get the date 1 month ago
$dateMonth = getDateMonth();
?>

<h2>Students on this course</h2>
<form name="process" action="process_targets2.php" method="POST">
    <table id="example" class="zebra-striped" style="text-align: center;">
        <thead>
        <tr>
            <th>Name</th>
            <th>Surname</th>
            <th>RAG</th>
            <th>ID</th>
            <th>Select</th>
            <th>Att %</th>
            <th>Targets</th>
            <th>Reviews</th>
            <th>Concerns</th>
            <th>Reasons</th>
            <th>Contribs</th>
            <th>T-MTG</th>
            <th>P-Best</th>
            <th>QCA</th>
            <th>MIS MTG</th>
            <th>R 1</th>
            <th></th>
            <th>R 2</th>
            <th></th>
            <th>R 3</th>
            <th></th>
            <th>R 4</th>
            <th>Parental</th>
            <th>Cast</th>
            <th>Withdrawn</th>
            <th>Mobile</th>
            <th>Medals</th>
        </tr>
        </thead>
        <tbody>

        <?php
        while ($row = $resultsstudents->fetch_object()) {

            // Get the stuff
            $queryrag = "SELECT status FROM mdl_ilpconcern_status WHERE userid='" . $row->id . "'";
            $resultrag = $mysqli->query($queryrag);

            $num_rows_rag = $resultrag->num_rows;
            //            echo $num_rows_rag;

            if ($num_rows_rag > 0) {
                while ($row2 = $resultrag->fetch_object()) {
                    $status = $row2->status;
                    echo $status;
                }
            } else
                $status = '0';

            if (($status == '0') or ($status == null)) {
                $colour = 'green';
                $ragicon = '<img src="images/1Green_Ball.png" title="1green" height="20px" width="20px"/>';
                $green++;
            } elseif ($status == '1') {
                $colour = 'amber';
                $ragicon = '<img src="images/2Yellow_Ball.png" title="2yellow" height="20px" width="20px"/>';
                $amber++;
            } elseif ($status == '2') {
                $ragicon = '<img src="images/3Red_Ball.png" title="3red" height="20px" width="20px"/>';
                $colour = 'red';
                $red++;
            }

            // Get the students targets
            list($tobe, $withdrawn, $acheived, $target_month, $target_month_with, $target_month_ach) = getTargets($row->id, $target_month, $target_month_with, $target_month_ach, $dateMonth, $mysqli);

            $activeTargets = $activeTargets + $tobe;
            $targetsAchieved = $targetsAchieved + $achieved;
            $targetsWithdrawn = $targetsWithdrawn + $withdrawn;

            // Get the student concersn, reviews etc
            list($reviews, $concerns, $reasons, $contributions, $month_review, $month_concern, $month_reason, $month_contribs, $studentsWithReviews, $studentsWithConcerns, $studentsWithReasons, $studentsWithContributions, $totalReviews, $totalConcerns, $totalReasons, $totalContributions) = getReviews($row->id, $month_review, $month_concern, $month_reason, $month_contribs, $dateMonth, $studentsWithReviews, $studentsWithConcerns, $studentsWithReasons, $studentsWithContributions, $totalReviews, $totalConcerns, $totalReasons, $totalContributions, $mysqli);


            // Attendance
            $totalAttendance = $client->__soapCall("attendanceGroupReport", array($row->idnumber));

            if ($totalAttendance >= '100') {
                $outstanding++;
            } elseif ($totalAttendance >= '95') {
                $excellent = $excellent + 1;
            } elseif ($totalAttendance <= '94' && $totalAttendance >= '90') {
                $good++;
            } elseif ($totalAttendance <= '89' && $totalAttendance >= '80') {
                $causeForConcern++;
            } elseif ($totalAttendance <= '80') {
                $poor++;
            }

            list($qca, $mtg_grade) = $client->__soapCall("getMTGMISAggregate", array($row->idnumber));


            list($mtg, $pbest) = getMTGS($row->idnumber, $mysqli);

            if (($mtg != '') or ($mtg != null)) {
                $mtg_set++;
            }

            //            Flightplan stuff

            list($review1, $r2, $review2, $r3, $review3, $r4, $review4) = getFlightplanScores($row->idnumber, $mysqli);

            // Work out the scores for the flight plans graphs

            for ($i = 1; $i <= 4; $i++) {
                if (${'review' . $i} == 1) {
                    ${'reviewOneScore' . $i}++;
                } elseif (${'review' . $i} == 2) {
                    ${'reviewTwoScore' . $i}++;
                } elseif (${'review' . $i} == 3) {
                    ${'reviewThreeScore' . $i}++;
                } elseif (${'review' . $i} == 4) {
                    ${'reviewFourScore' . $i}++;
                } elseif (${'review' . $i} == 5) {
                    ${'reviewFiveScore' . $i}++;
                } elseif (${'review' . $i} == 6) {
                    ${'reviewSixScore' . $i}++;
                } elseif (${'review' . $i} == '') {
                    ${'noflight' . $i}++;
                }
            }

            list($mobile, $studentWithdrawn, $parental, $castSupport) = $client->__soapCall("getAggregateGroup", array($row->idnumber));

//            echo 'mob ' , $mobile , ' with ' , $studentWithdrawn , ' parental ' , $parental , ' cast ' , $castSupport;
            if ($castSupport == 1) {
                $cast_signed++;
            }

            if ($parental == 1) {
                $parental_signed++;
            }

            if ($parental == 2) {
                $parental_na++;
            }


            $badges = getMedals($row->idnumber);

            ?>
        <tr>
            <td><a
                href="<?php echo $CFG->wwwroot; ?>/blocks/ilp/view.php?courseid=<?php echo $_SESSION['course_code_session']; ?>&id=<?php echo $row->id; ?>"><?php echo $row->firstname; ?></a>
            </td>
            <td><a
                href="<?php echo $CFG->wwwroot; ?>/blocks/ilp/view.php?courseid=<?php echo $_SESSION['course_code_session']; ?>&id=<?php echo $row->id; ?>"><?php echo $row->lastname; ?></a>
            </td>
            <td><?php echo $ragicon; ?></td>
            <td><?php echo $row->idnumber; ?></td>
            <td><input type="checkbox" class="checkbox" name="checkbox[]" value="<?php echo $row->id; ?>"></td>
            <td><?php echo $totalAttendance; ?></td>

            <td><?php echo $tobe; ?>/<?php echo $acheived; ?>/<?php echo $withdrawn; ?></td>
            <td><?php echo $reviews; ?></td>
            <td><?php echo $concerns; ?></td>
            <td><?php echo $reasons; ?></td>
            <td><?php echo $contributions; ?></td>
            <td><?php echo $pbest; ?></td>
            <td><?php echo $mtg; ?></td>
            <td><?php echo $qca; ?></td>
            <td><?php echo $mtg_grade; ?></td>
            <td><?php echo $review1; ?>
            <td><?php echo $r2; ?></td>
            <td><?php echo $review2; ?></td>
            <td><?php echo $r3; ?></td>
            <td><?php echo $review3; ?></td>
            <td><?php echo $r4; ?></td>
            <td><?php echo $review4; ?></td>
            <td><?php echo checkIfTrue($parental); ?></td>
            <td><?php echo checkIfTrue($castSupport); ?></td>
            <td><?php echo checkIfTrue($studentWithdrawn); ?></td>
            <td><?php echo checkIfTrue($mobile); ?></td>
            <td>
                <?php
                foreach ($badges as $row) {
                    echo '<img src="' . $CFG->wwwroot . '/blocks/ilp/templates/custom/badges/images/' . $row . '.png" width="25" height=25" />';
                } ?>
            </td>
        </tr>
            <?php $count++; ?>
            <?php } ?>
        </tbody>
    </table>



<?php // select options ?>


<div class="row">
<div class="container">
<h3>Select type and Set</h3>
<fieldset>
<div class="clearfix">
    <label for="select_review" id="review_title">Select Review</label>

    <div class="input pad">
        <select name="type" id="select_review">
            <option>--Select--</option>
            <option>Target</option>
            <option>Progress Review</option>
            <option>Concern</option>
            <option>Reason for Status Change</option>
            <option>Contribution</option>
            <option>RAG - Traffic Light</option>
            <option>Medals</option>
            <option>Progression Targets</option>
            <option>Employability Passport</option>
        </select>
    </div>
</div>


</p>

<div class="clearfix">
    <label for="rag" id="rag_title">Select RAG</label>

    <div class="input pad">
        <select name="rag" id="rag">
            <option>--Select--</option>
            <option>Green</option>
            <option>Amber</option>
            <option>Red</option>
        </select>
    </div>
</div>

<div class="clearfix">
    <label for="target_name" id="target_name_title">Target Name</label>

    <div class="input pad">
        <input id="target_name" type="text" name="title" size="52"/>
    </div>
</div>

<div class="demo">
    <div class="clearfix">
        <label for="datepicker" id="datepicker_title">Select Date</label>

        <div class="input pad">
            <input type="text" id="datepicker" name="date">
        </div>
    </div>
</div>

<div class="clearfix">
    <label for="details" id="details_title">Enter Details</label>

    <div class="input pad">
        <textarea name="target" rows="8" cols="40" id="details"></textarea>
    </div>
</div>

<div class="clearfix">
    <label for="checkbox" id="checkbox_title">The target is related to this course</label>

    <div class="input pad">
        <input type="checkbox" id="checkbox" name="course_related" value="ON" checked/>
    </div>
</div>


<div id="medals_div">

    <?php
    $badgeCount == 0;
//    mysql_select_db('medals') or die('Unable to select the database');
    $querymedals = "SELECT * FROM badges";
    $resultsbadges = $mysqli->query($querymedals);

    $num_rows = $resultsbadges->num_rows;
    //echo 'num rows: ' . $num_rows;
    echo '<h3>Select medals</h3>';
//        echo '**Warning the student must have manual mtg set on the flightplan for medals to work**';
    echo '<table>';
    while ($row = $resultsbadges->fetch_object()) {

        if ($badgeCount == 0) {
            echo '<tr><td>' . $row->name . ' ';
            echo '</td><td><img src="http://' . $domain . '/blocks/ilp/templates/custom/badges/images/' . $row->icon . '.png"/></td>';
            echo '<td>';
            //<input type="checkbox" id="checkbox_medal" name="checkbox_medal[]" value="' . $row['id'] . '" />';
            echo '<input type="radio" name="medal" value="' . $row->id . '"   />';
            echo '</td>';

        } else {
            echo '<td width="20px"></td><td>' . $row->name . ' ';
            echo '</td><td><img src="http://' . $domain . '/blocks/ilp/templates/custom/badges/images/' . $row->icon . '.png"/></td>';

            echo '<td>';
            //<input type="checkbox" id="checkbox_medal" name="checkbox_medal[]" value="' . $row['id'] . '" />';
            echo '<input type="radio" name="medal" value="' . $row->id . '"   />';
            echo '</td></tr>';
        }
        //        echo 'badge count is ' . $badgeCount;
        if ($badgeCount == 0) {
            $badgeCount = 1;
        } elseif ($badgeCount == 1) {
            $badgeCount = 0;
        }
    }
    echo '</table>';
    ?>
</div>


<div id="employability">
    <div class="clearfix">
        <label id="employ" accesskey="">Select employability options to mark completed</label>
        <table style="text-align:center;">
            <tr class="bronze">
                <td>
                    Bronze 1
                </td>
                <td>
                    Bronze 2
                </td>
                <td>
                    Bronze 3
                </td>
            </tr>
            <tr class="bronze">
                <td>
                    Professional Standards
                </td>
                <td>
                    Professional Communication
                </td>
                <td>
                    Draft CV
                </td>
            </tr>
            <tr class="silver">
                <td>
                    <input type="checkbox" id="b1" name="b1">
                </td>
                <td>
                    <input type="checkbox" id="b2" name="b2">
                </td>
                <td>
                    <input type="checkbox" id="b3" name="b3">
                </td>
            </tr>

            <tr class="silver">
                <td>Silver 1</td>
                <td>Silver 2</td>
                <td>Silver 3</td>
            </tr>
            <tr class="silver">
                <td>Searching for a job</td>
                <td>Employer Interview</td>
                <td>Create a CV</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" id="s1" name="s1">
                </td>
                <td>
                    <input type="checkbox" id="s2" name="s2">
                </td>
                <td>
                    <input type="checkbox" id="s3" name="s3">
                </td>

            </tr>

            <tr class="gold">
                <td>Gold 1</td>
                <td>Gold 2</td>
                <td>Gold 3</td>
            </tr>
            <tr class="gold">
                <td>Optional</td>
                <td>Optional</td>
                <td>Optional</td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" id="g1" name="g1">
                </td>
                <td>
                    <input type="checkbox" id="g2" name="g2">
                </td>
                <td>
                    <input type="checkbox" id="g3" name="g3">
                </td>
            </tr>
        </table>

    </div>
</div>

<!-- pass the course id and userid -->
<input type="hidden" name="courseid" value=" <?php echo $_SESSION['course_code_session'] ?> "/>
<input type="hidden" name="userid" value=" <?php echo $USER->id ?> "/>
<input type="hidden" name="username" value=" <?php echo $USER->username ?> "/>
<!--    <input type="hidden" name="url" value=" --><?php //echo $url ?><!-- "/>-->
<!--    <input type="hidden" name="url2" value=" --><?php //echo $url2 ?><!-- "/>-->

<br/>

<input id="save" class="btn success" type="submit" name="submit_change" value="Submit Changes"/>

</form>
</div>
</fieldset>
</div>
</div>



<!-- graphs and numbers -->
<div id="totals">
    <h2>Totals</h2>
    <table style="text-align: center;  margin-left: auto; margin-right: auto;" class="totals">
        <tr>
            <th>Active Targets</th>
            <th>Targets Achieved</th>
            <th>Targets Withdrawn</th>
        </tr>
        <td><?php echo $activeTargets; ?></td>
        <td><?php echo $targetsAchieved; ?></td>
        <td><?php echo $targetsWithdrawn; ?></td>
        </tr>
    </table>

    <table style="text-align: center;  margin-left: auto; margin-right: auto;" class="totals">
        <tr>
            <th colspan='2'>Reviews</th>
            <th colspan='2'>Concerns</th>
            <th colspan='2'>Reason for Status Change</th>
            <th colspan='2'>Contributions</th>
        </tr>
        <tr>
            <th>Students with Reviews</th>
            <th>Total Reviews</th>
            <th>Students with Concerns</th>
            <th>Total Concerns</th>
            <th>Students with Reasons</th>
            <th>Total Reasons</th>
            <th>Students with Contributions</th>
            <th>Total Contributions</th>
        </tr>
        <tr>
            <td><?php echo $studentsWithReviews; ?></td>
            <td><?php echo $totalReviews; ?></td>
            <td><?php echo $studentsWithConcerns; ?></td>
            <td><?php echo $totalConcerns; ?></td>
            <td><?php echo $studentsWithReasons; ?></td>
            <td><?php echo $totalReasons; ?></td>
            <td><?php echo $studentsWithContributions; ?></td>
            <td><?php echo $totalContributions; ?></td>
        </tr>
    </table>





    <?php

//flightplan scores 1
    $graph1 = array(
        $reviewOneScore1,
        $reviewTwoScore1,
        $reviewThreeScore1,
        $reviewFourScore1,
        $reviewFiveScore1,
        $reviewSixScore1,
        $noflight1,
    );


    $graph2 = array(
        $reviewOneScore2,
        $reviewTwoScore2,
        $reviewThreeScore2,
        $reviewFourScore2,
        $reviewFiveScore2,
        $reviewSixScore2,
        $noflight2,
    );

//flightplan scores
    $graph3 = array(
        $reviewOneScore3,
        $reviewTwoScore3,
        $reviewThreeScore3,
        $reviewFourScore3,
        $reviewFiveScore3,
        $reviewSixScore3,
        $noflight3,
    );

//flightplan scores
    $graph4 = array(
        $reviewOneScore4,
        $reviewTwoScore4,
        $reviewThreeScore4,
        $reviewFourScore4,
        $reviewFiveScore4,
        $reviewSixScore4,
        $noflight4,
    );

    ?>
    <h3>Fancy charts of great import</h3>
    <?php

    $colours = array('#FF6600', '#FFCC00', '#FFFF00', '#33FF66', '#33CC33', '#339900', '#FF0000');
    makePieChart($graph1, $colours, 'Review 1');
    makePieChart($graph2, $colours, 'Review 2');
    makePieChart($graph3, $colours, 'Review 3');
    makePieChart($graph4, $colours, 'Review 4');


    $graphAtt = array(
        $outstanding,
        $excellent,
        $good,
        $causeForConcern,
        $poor,

    );

    $colours = array('#339900', '#33FF66', '#FFCC00', '#FF6600', '#FF0000');
    $legend = array('Outstanding', 'Excellent', 'Good', 'Concern', 'Poor');
    makePieChart2($graphAtt, $legend, $colours, 'Attendance');


    // RAG pie charts
    $graph = array(
        $green,
        $amber,
        $red,
    );

    $colours = array('#2AFF2A', '#FFD400', '#FF0000');
    $legend = array('Green', 'Amber', 'Red');
    makePieChart2($graph, $legend, $colours, 'RAG Status');

    $mtg_not_set = $count - $mtg_set;
    $graph = array(
        $mtg_set,
        $mtg_not_set,
    );

    $colours = array('#31B131', '#FF0000');
    $colours2 = array('#31B131', '#87AACB','#FF0000');
    $legend = array('MTG Set', 'MTG Not Set');
    makePieChart2($graph, $legend, $colours, 'Manual MTGs Set');

    $parental_not_signed = $count - ($parental_signed +  $parental_na);
    $graph = array(
        $parental_signed,
        $parental_na,
        $parental_not_signed,
    );

    $legend = array('Signed', 'N/A', 'Not Signed');
    makePieChart2($graph, $legend, $colours2, 'Parental Agreements');

    $cast_not_signed = $count - $cast_signed;
    $graph = array(
        $cast_signed,
        $cast_not_signed,
    );

    $legend = array('Support', 'No Support');
    makePieChart2($graph, $legend, $colours, 'Cast Support');

    ?>
</div>
</div>
<?php
include('bottom_include.php');
$mysqli->close;
//mysql_close($link);

?>

<script>
    $(document).ready(function () {
        var oTable = $('#example').dataTable({
            "aoColumns":[
                null,
                null,
                { "sType":'string' },
                null,
                { "sType":'numeric' },
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                { "sType":'numeric' },
                { "sType":'numeric' },
                { "sType":'string' },
                { "sType":'numeric' },
                { "sType":'string' },
                { "sType":'numeric' },
                { "sType":'string' },
                { "sType":'string' },
                { "sType":'string' },
                { "sType":'string' },
                { "sType":'string' },
                { "sType":'string' },
                null
            ],
            "sDom":'<"H"Rfr>t<"F"i>',

//        "sDom": '<"clear"lfr>t<ip>',
            "bJQueryUI":true,
            "bPaginate":false
        });

    });
</script>


<script type="text/javascript">
    $(function () {
        $('#accordion').accordion({
            collapsible:true
        });
    });
</script>

<script type="text/javascript">
    //    $(function () {
    //        $('#multiOpenAccordion3').multiAccordion({ active:false });
    //    });


    $(function () {
        $("#datepicker").datepicker({
            dateFormat:'dd-mm-yy',
            changeMonth:true,
            changeYear:true
        });
        // tl is the default so don't bother setting it's positio
    });


    $(function () {
        //initially hide the textbox
        $("#target_name").hide();
        $("#target_name_title").hide();
        $("#datepicker").hide();
        $("#datepicker_title").hide();
        $("#rag").hide();
        $("#rag_title").hide();
        $("#details").hide();
        $("#details_title").hide();
        $("#checkbox").hide();
        $("#checkbox_title").hide();
        $("#medals_div").hide();
        $("#medals_title").hide();
        $("#medal").hide();
        $("#employability").hide();
        $("#save").hide();
        $('#select_review').change(function () {
            if ($(this).find('option:selected').val() == "Target") {
                $("#target_name").show();
                $("#datepicker").show();
                $("#datepicker_title").show();
                $("#target_name_title").show();
                $("#rag").hide();
                $("#rag_title").hide();
                $("#details").show();
                $("#details_title").show();
                $("#checkbox").show();
                $("#checkbox_title").show();
                $("#employability").hide();
                $("#save").show();
            } else if ($(this).find('option:selected').val() == "RAG - Traffic Light") {
                $("#rag").show();
                $("#rag_title").show();
                $("#target_name").hide();
                $("#datepicker").hide();
                $("#target_name_title").hide();
                $("#datepicker_title").hide();
                $("#details").show();
                $("#details_title").show();
                $("#checkbox").hide();
                $("#checkbox_title").hide();
                $("#medals_div").hide();
                $("#employability").hide();
                $("#save").show();

            } else if ($(this).find('option:selected').val() == "Progression Targets") {
                $("#rag").hide();
                $("#rag_title").hide();
                $("#target_name").hide();
                $("#datepicker").show();
                $("#target_name_title").hide();
                $("#datepicker_title").show();
                $("#details").show();
                $("#details_title").show();
                $("#checkbox").hide();
                $("#checkbox_title").hide();
                $("#medals_div").hide();
                $("#employability").hide();
                $("#save").show();

            } else if ($(this).find('option:selected').val() == "Medals") {

                $("#medals_div").show();
                $("#target_name").hide();
                $("#datepicker").hide();
                $("#target_name_title").hide();
                $("#datepicker_title").hide();
                $("#details").hide();
                $("#details_title").hide();
                $("#checkbox").hide();
                $("#checkbox_title").hide();
                $("#rag").hide();
                $("#rag_title").hide()
                $("#employability").hide();
                $("#save").show();
            } else if ($(this).find('option:selected').val() == "Employability Passport") {

                $("#medals_div").hide();
                $("#target_name").hide();
                $("#datepicker").hide();
                $("#target_name_title").hide();
                $("#datepicker_title").hide();
                $("#details").hide();
                $("#details_title").hide();
                $("#checkbox").hide();
                $("#checkbox_title").hide();
                $("#rag").hide();
                $("#rag_title").hide()
                $("#employability").show();
                $("#save").show();
            } else if ($(this).find('option:selected').val() == "--Select--") {
                $("#target_name").hide();
                $("#target_name_title").hide();
                $("#datepicker").hide();
                $("#datepicker_title").hide();
                $("#rag").hide();
                $("#rag_title").hide();
                $("#details").hide();
                $("#details_title").hide();
                $("#checkbox").hide();
                $("#checkbox_title").hide();
                $("#medals_div").hide();
                $("#medals_title").hide();
                $("#medal").hide();
                $("#employability").hide();
                $("#save").hide();
            } else {
                $("#target_name").hide();
                $("#datepicker").hide();
                $("#target_name_title").hide();
                $("#datepicker_title").hide();
                $("#rag").hide();
                $("#rag_title").hide();
                $("#details").show();
                $("#details_title").show();
                $("#checkbox").show();
                $("#checkbox_title").show();
                $("#medals_div").hide();
                $("#employability").hide();
                $("#save").show();
            }
        });

    });

</script>