<?php
/**
 * Created by JetBrains PhpStorm.
 * User: DATTWOOD
 * Date: 22/10/11
 * Time: 12:14
 * To change this template use File | Settings | File Templates.
 */
 
include('../config.php');


$query = "select * from mdl_user where username  like 'G%' AND description = 'Acoount created as part of Guardian insert' and idnumber=firstname";
echo $query;
$result = mysql_query($query);

$num_rows = mysql_num_rows($result);

echo $num_rows;
while ($row = mysql_fetch_assoc($result)) {
    $idnumber = $row['idnumber'];
    $id = $row['id'];
echo $idnumber . '<br/>';

        echo 'a hit<br/>';
echo 'idnumber: ' . $idnumber;
        $one = substr($idnumber, 0 , 2);
        $two = substr($idnumber, 2);

       $newIdNumber = $one . '-' . $two;

               echo ' becomes ' . $newIdNumber . '<br/>';
        
$queryUpdate = "UPDATE mdl_user SET firstname='". $newIdNumber . "', email='nobody@midkent.ac.uk' WHERE id='" . $id . "'";
        echo $queryUpdate;

        mysql_query($queryUpdate);
        echo '<br/>';
    
}


?>