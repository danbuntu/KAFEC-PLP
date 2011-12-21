<?php
/**
 * Created by JetBrains PhpStorm.
 * User: DATTWOOD
 * Date: 20/10/11
 * Time: 17:02
 * To change this template use File | Settings | File Templates.
 */

include('../config.php');
$query = "SELECT username FROM mdl_user where deleted != 1 and (substr(username,1,1) in ('0','1','2','3','4','5','6','7','8','9') and substr(username,1,2) != '11') order by username";
echo $query;
$result = mysql_query($query);


while ($row = mysql_fetch_array($result)) {
    $username = $row['username'];

      $count = '';
    $query2 = "select * from mdl_user where idnumber ='" . $username . "'";
    echo $query2;

    $result2 = mysql_query($query2);
    $num_rows = mysql_num_rows($result2);

    echo $num_rows . '<br/>';

$count++;
}




//select * from `moodle`.`mdl_user` where username = (
//SELECT username FROM `moodle`.`mdl_user` where deleted != 1 and (substr(username,1,1) in ('0','1','2','3','4','5','6','7','8','9') and substr(username,1,2) != '11') order by username





?>