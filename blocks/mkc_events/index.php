<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include('database_conection.php');

echo '<h2>Moodle Events</h2>';

$date = date("d-m-y");
echo 'todays date is: ' . $date . '<br>';

echo '<a href=add_event.php><img src="images/add-1-icon.png" border="0" width="34" height="34" alt="Add an Event"/>Add a new event</a>';

//display all events

$query = "SELECT * FROM events ORDER BY enddate ASC";

$result = mysql_query($query);

echo '<table>';
echo '<tr><th>ID</th><th>Name</th><th>Religion</th><th>Details</th><th>From date</th><th>End date</th></tr>';

while ($row = mysql_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['id'] . '</td>';
    echo '<td>' . $row['name'] . '</td>';
    echo '<td>' . $row['religion'] . '</td>';
    echo '<td>' . $row['details'] . '</td>';
    echo '<td>' . date("d-m-Y", strtotime($row['startdate'])) . '</td>';
    echo '<td>' . date("d-m-Y", strtotime($row['enddate'])) . '</td>';
    echo '<td><a href="./edit.php?id=' . $row['id'] . '"><img src="images/edit-icon.png" border="0" width="34" height="34" alt="Edit Event"/>Edit</a></td>';
     echo '<td><a href="./delete.php?id=' . $row['id'] . '"><img src="images/delete-icon.png" border="0" width="34" height="34" alt="Delete Eveny"/>Delete</a></td>';
    echo '</tr>';
}
echo '</table>';


?>
