<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bootstrap, from Twitter</title>
    <meta name="description" content="">
    <meta name="author" content="">


    <?php

    require_once("../../config.php");
    include('soap_connection.php');
    global $USER, $CFG;
    ?>
    <script type="text/javascript" src="./jquery2/jquery-1.7.min.js"></script>
    <script type="text/javascript" src="./jquery2/jquery-ui-1.8.16.custom.min.js"></script>
    <script type="text/javascript" src="./jquery2/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="./jquery2/ColReorder.min.js"></script>
    <script type="text/javascript" src="./jquery2/ColVis.min.js"></script>
    <script type="text/javascript" src="./jquery2/jquery.multi-open-accordion-1.0.1.js"></script>

    <link rel="stylesheet" href="twitter-bootstrap/bootstrap.min.css" type="text/css" media="screen"  charset="utf-8">
    <link rel="stylesheet" href="./jquery2/css/custom-theme/jquery-ui-1.8.16.custom.css" type="text/css" media="screen"  charset="utf-8">
    <link rel="stylesheet" href="accordcss.css" type="text/css" media="screen"  charset="utf-8">
    <link rel="stylesheet" href="styles2.css" type="text/css" media="screen"  charset="utf-8">
        <?php

    include('moodle_connection_mysqli.php');
    include('shared_functions.php');
    include('accord_functions2.php');


    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    ?>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
</head>


<!--[if lt IE 9]>
<script src="jquery2/html5.js"></script>
<![endif]-->

<link href="../bootstrap/bootstrap.min.css" rel="stylesheet">
<style type="text/css">
    body {
        padding-top: 60px;
    }
</style>


<?php

// set session variables

// set the academic year if needed as a session varible
if (empty($_SESSION['academicYear'])) {
    $resultAtt = $client->__soapCall("getAcademicYear", array(''));
    foreach ($resultAtt as $item) {
        $_SESSION['academicYear'] = $item['academicyear'];
    }
}

if (!empty($_GET['courseid'])) {
    $_SESSION['course_code_session'] = $_GET['courseid'];
}

if (!empty($_GET['var1'])) {
    $_SESSION['course_context_session'] = $_GET['var1'];
}


?>
