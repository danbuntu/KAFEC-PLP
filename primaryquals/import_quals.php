<?php

/*
 * Import a csv called student.csv into the primary_qual table
 * data then pulled via the plp tio show MTG
 */

include('database_connection.php');

$fh = fopen("students.csv", "r");

while (list($learner_code, $nvqcode, $aim, $main_qual) = fgetcsv($fh, 1024, ",")) {
    //output
    echo '<table><tr><td>';
    echo "learnercode: " . $learner_code . '<br>';
    echo '</td><td>';
    echo "nvg: " . $nvqcode . '<br/>';
    echo '</td></tr><tr><td>';
    echo "aim: " . $aim . '<br/>';
    echo '<td></tr><tr>';
    echo "main qual: " . $main_qual . '<br/>';
    echo '</td></tr>';


    //mysql stuff
    //check if the user is already in the database
    $query = "SELECT * FROM primary_qual WHERE learner_code='" . $learner_code . "'";

    $result = mysql_query($query);
    $num_rows = mysql_num_rows($result);

    echo '<tr><td>';
    echo $query . '<br/>';
    echo $num_rows;
    echo '</td></tr>';

    //check for a returned row to to confirm they exist
    if ($num_rows <= 0) {
       
        //if they dont exist add them to the database
        $queryadd = "INSERT INTO primary_qual (learner_code, primary_qual, nvqcode, aim) VALUES ('" . $learner_code . "','" . $main_qual . "','" . $nvqcode . "','" . $aim . "')";
       echo '<tr><td>';
        echo '<font color=green>' . $queryadd . '</font>';
        echo '</td></tr></table>';
        echo '<br>';
        mysql_query($queryadd) or die(mysql_error());
    } else {
        //if the records does exist update it
        //get the stundet id int he database
        $queryid = "SELECT id FROM primary_qual WHERE learner_code='" . $learner_code . "'";
        
        //echo  '<font colour=red>' . $queryid . '</font>';
        
        $id = mysql_query($queryid);


        while ($row = mysql_fetch_array($id)) {
           // echo '<br>';
           // echo 'id query' . $row['0'];
            $id2 = $row['0'];
            //echo '<br/>';
        }

        $queryupdate = "UPDATE primary_qual SET primary_qual='" . $main_qual . "' WHERE id='" . $id2 . "'";
        echo '<tr><td>';
        echo '<font color=red>' . $queryupdate . '</font><br/>';
        echo '</td></tr></table>';
        echo '<br>';
        mysql_query($queryupdate);
    }

    //probably should have something here to clean out old records as well
}


mysql_close($linkpri);
?>



