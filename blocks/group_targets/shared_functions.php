<?php
/**
 * Created by JetBrains PhpStorm.
 * User: DATTWOOD
 * Date: 07/12/11
 * Time: 13:29
 * To change this template use File | Settings | File Templates.
 */


function topbar($menuItem, $navItems)
{

    // Contains the nav bar items
    $navItems = array(
        'Group Target Setter' => '/blocks/group_targets/view2.php',
        'Change Target Status' => '/blocks/group_targets/set2.php',
        'Import Feeds' => $siteUrl . '/jobshop/feeds/process_feeds.php',
        'View Applications' => $siteUrl . '/jobshop/processing/index.php',
        'Featured Jobs' => $siteUrl . '/jobshop/slider/index.php',
    );


    echo '<div class="topbar">';
    echo '<div class="topbar-inner">';
    echo '<div class="container-fluid">';
    echo '<a class="brand" href="#">Group Target Setter v2.0</a>';
    echo '<ul class="nav">';
    //print_r($navItems);
    foreach ($navItems as $key => $item) {

        //        echo $key . ' ' . $item;

        if ($menuItem != $key) {
            $active = '';
        } elseif ($menuItem == $key) {
            $active = ' class="active"';
        }

        echo '<li  ', $active, '><a href="', $item, '">', $key, '</a></li>';
    }

    echo '</ul>';
//    echo '<p class="pull-right">Logged in as <a href="#">username</a></p>';';
    echo '</div>';
    echo '</div>';
    echo '</div>';

}


function getCourseContextID($courseId, $mysqli)
{
    $query = "SELECT id FROM mdl_context WHERE instanceid='" . $courseId . "' AND contextlevel='50'";
    $result = $mysqli->query($query);
    while ($row = $result->fetch_object()) {
        $courseContextId = $row->id;
    }
    return $courseContextId;
}

// Get the date 1 month ago
function getDateMonth()
{
    $dateMonth = strtotime("-1 month");
    return $dateMonth;
}


// Get the students review, concern etc numbers
function getReviews($studentMoodleId, $month_review, $month_concern, $month_reason, $month_contribs, $dateMonth, $studentsWithReviews, $studentsWithConcerns, $studentsWithReasons, $studentsWithContributions, $totalReviews, $totalConcerns, $totalReasons, $totalContributions, $mysqli)
{
    $query = "SELECT status, timemodified FROM mdl_ilpconcern_posts WHERE setforuserid='" . $studentMoodleId . "'";
    $result = $mysqli->query($query);

    $review = 0;
    $concern = 0;
    $reason = 0;
    $contribution = 0;

    while ($row = $result->fetch_object()) {
        if ($row->status == 0) {
            $review = $review + 1;
            if ($row->timemodified > $dateMonth) {
                $month_review = $month_review + 1;
            }
        } elseif ($row->status == 1) {
            $concern = $concern + 1;
            if ($row->timemodified > $dateMonth) {
                $month_concern = $month_concern + 1;
            }
        } elseif ($row->status == 2) {
            $reason = $reason + 1;
            if ($row->timemodified > $dateMonth) {
                $month_reason = $month_reason + 1;
            }
        } elseif ($row->status == 3) {
            $contribution = $contribution + 1;
            if ($row->timemodified > $dateMonth) {
                $month_contribs = $month_contribs + 1;
            }
        }
    }

    if ($review !== 0) {
        $studentsWithReviews++;
    }

    if ($concern !== 0) {
        $studentsWithConcerns++;
    }

    if ($reason !== 0) {
        $studentsWithReasons++;
    }

    if ($contribution !== 0) {
        $studentsWithContributions++;
    }

    $totalReviews = $totalReviews + $review;
    $totalConcerns = $totalConcerns + $concern;
    $totalReasons = $totalReasons + $reason;
    $totalContributions = $totalContributions + $contribution;

    $reviews = Array(
        $review, $concern, $reason, $contribution, $month_review, $month_concern, $month_reason, $month_contribs, $studentsWithReviews, $studentsWithConcerns, $studentsWithReasons, $studentsWithContributions, $totalReviews, $totalConcerns, $totalReasons, $totalContributions
    );

    //    print_r($reviews);
    return $reviews;
}


// Get the students targets
function getTargets($studentMoodleId, $target_month, $target_month_with, $target_month_ach, $dateMonth, $mysqli)
{
    $query = "SELECT status, timemodified FROM mdl_ilptarget_posts  WHERE setforuserid='" . $studentMoodleId . "'";
    //     echo $query;
    $tobe = 0;
    $achieved = 0;
    $withdrawn = 0;
    $result = $mysqli->query($query);
    while ($row = $result->fetch_object()) {
        $status = $row->status;
        if ($status == '0') {
            $tobe = $tobe + 1;
            if ($row->timemodified > $dateMonth) {
                $target_month = $target_month + 1;
            }
        } elseif ($status == '3') {
            $withdrawn = $withdrawn + 1;
            if ($row->timemodified > $dateMonth) {
                $target_month_with = $target_month_with + 1;
            }
        } elseif ($status == '1') {
            $achieved = $achieved + 1;
            if ($row->timemodified > $dateMonth) {
                $target_month_ach = $target_month_ach + 1;
            }
        }
    }
    $targets = Array(
        $tobe, $withdrawn, $achieved, $target_month, $target_month_with, $target_month_ach
    );
    return $targets;
}

function getMTGS($studentId, $mysqli)
{
    $query = "SELECT mtg, tutor_mtg FROM moodle.mtg WHERE student_id='" . $studentId . "'";
    //    echo $query;
    $result = $mysqli->query($query);
    $mtgArray = array();
    while ($row = $result->fetch_object()) {
        array_push($mtgArray, $row->mtg);
        array_push($mtgArray, $row->tutor_mtg);
    }

    return $mtgArray;
}


function getFlightplanScores($studentId, $mysqli)
{

    if ($studentId != '') {
        // select the last 4 reviews{}

        $query = "SELECT score FROM flightplan WHERE student_id='" . $studentId . "' ORDER BY id DESC LIMIT 4";
        //    echo $query;
        $result = $mysqli->query($query);

        $num_rows = $result->num_rows;

        $i = $num_rows;
        while ($row = $result->fetch_object()) {

            ${review . $i} = $row->score;
            $i--;
        }

        $r2 = compareReviews($review1, $review2);
        $r3 = compareReviews($review2, $review3);
        $r4 = compareReviews($review3, $review4);

        $reviews = array(
            $review1, $r2, $review2, $r3, $review3, $r4, $review4
        );

        //    print_r($reviews);
        return $reviews;
    }
}

// Compare the two reviews and set the colour to denote improvement or worsening
function compareReviews($one, $two)
{
    if (($one == '') or ($two == '')) {
        $reviewGraphic = '<img src="./images/list-remove.png" height"20px" width="20px">';
    } else {
        if ($two < $one) {
            //            $reviewColour = 'red';
            $reviewGraphic = '<img src="./images/go-down.png" height"20px" width="20px">';
        } elseif ($two > $one) {
            //            $reviewColour = 'green';
            $reviewGraphic = '<img src="./images/go-up.png" height"20px" width="20px">';
        } elseif ($two == $one) {
            //            $reviewColour = 'amber';
            $reviewGraphic = '<img src="./images/go-next.png" height"20px" width="20px">';
        }
    }


    //    array_push($reviews, $reviewColour);
    return $reviewGraphic;
}


function checkIfTrue($value)
{
    if ($value == 1) {
        echo '<img src="./images/tick-icon.png" height"20px" width="20px"/>';
    } elseif ($value == 2) {
        echo 'N/A';
    } else {
        echo '<img src="./images/delete-icon.png" height"20px" width="20px"/>';
    }
}


// Get the medals awarded to the student
function getMedals($studentNumber)
{
    include('moodle_connection_mysqli.php');
    //    $query = "SELECT * FROM medals.students s JOIN medals.badges_link b ON b.student_id=s.id JOIN medals.badges bb ON bb.id=b.badge_id WHERE s.students_name='" . $username . "'";
    $query = "SELECT * FROM badges_link  JOIN badges ON badges_link.badge_id=badges.id  where student_id='" . $studentNumber . "'";

    //     echo $query;
    $result = $mysqli->query($query);
    $badges = array();
    while ($row = $result->fetch_object()) {
        $icon = $row->icon;
        array_push($badges, $icon);
    }
    $mysqli->close();
    return $badges;

}


function makePieChart($array, $colours, $title)
{
    $i = 0;
    $legend = array();
    foreach ($array as $item) {
        if ($i != 6) {
            $key = $i . ' (' . $item . ')';
            array_push($legend, $key);
            $i++;
        } else {
            $key = 'No flight set (' . $item . ')';
            array_push($legend, $key);
        }
    }

    echo '<img src="graphs2.php?var1=', urlencode(serialize($array)), '&var2=', $title, '&var3=' , urlencode(serialize($legend)) , '&var4=' ,urlencode(serialize($colours))  , '">';
}


function makePieChart2($array, $legend, $colours, $title)
{
    $merged = array_combine($legend, $array);

    $legend2 = array();
    foreach ($merged as $key=> $item) {
        $value = $key . ' (' . $item . ')';
        array_push($legend2, $value);
    }

    echo '<img src="graphs2.php?var1=', urlencode(serialize($array)), '&var2=', $title, '&var3=' , urlencode(serialize($legend2)) , '&var4=' ,urlencode(serialize($colours))  , '">';
}

?>