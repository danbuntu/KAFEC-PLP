<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include('database_conection.php');
?>

<script type="text/javascript" src="./jquery/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="./jquery/js/jquery-ui-1.8.6.custom.min.js"></script>
<link rel="stylesheet" href="./jquery/css/ui-lightness/jquery-ui-1.8.6.custom.css" />
<link rel="stylesheet" href="./group_targets.css" />

<?php

$id = $_GET['id'];

echo '<h2>Edit ' . $id . '</h2>';

$query = "SELECT * FROM events WHERE id='" . $id . "'";

$result = mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
    echo '<form action="process_edit.php" method="POST">';

    echo 'Name <input type="text" name="name" value="' . $row['name'] . '" /><br/>';
    echo 'Religion<select name="religion">';
    echo '<option>' . $row['religion'] . '</option><br/>';
    echo '<option>Muslim</option>';
    echo '<option>Jewish</option>';
    echo '<option>Christian</option>';
    echo '<option>Pagan</option>';
    echo '</select><br/>';

    echo 'Details<textarea name="details" rows="4" cols="20">' . $row['details'];
    echo '</textarea><br/>';
    echo '<div class="demo">';
    echo 'From date<input type="text" id="datepicker" name="from_date" value="' . date("d-m-Y", strtotime($row['startdate'])) . '" /><br/>';
    echo '</div>';
    echo '<div class="demo">';
    echo 'From date<input type="text" id="datepicker2" name="to_date" value="' . date("d-m-Y", strtotime($row['enddate'])) . '" /><br/>';
    echo '</div>';


    echo '<input type="hidden" name="id" value="' . $id . '" />';
    echo '<input type="submit" value="submit" />';
    echo '</form>';
?>
    <script>
        $(function() {
            $( "#datepicker" ).datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });
    </script>

    <script>
        $(function() {
            $( "#datepicker2" ).datepicker({
                dateFormat: 'dd-mm-yy'
            });
        });
    </script>
<?php

}
?>
