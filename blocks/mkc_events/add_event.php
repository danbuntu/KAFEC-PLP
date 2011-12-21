<?php
/*
 * Add an event to the events database
 */
?>
<script type="text/javascript" src="./jquery/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="./jquery/js/jquery-ui-1.8.6.custom.min.js"></script>
<link rel="stylesheet" href="./jquery/css/ui-lightness/jquery-ui-1.8.6.custom.css" />
<link rel="stylesheet" href="./group_targets.css" />

<?php

echo '<h2>Add an event to the Events database</h2>';

echo '<form action="process_event.php" method="POST" >';

echo 'Event Name<input type="text" name="name" value="" /><br/>';
echo '<select name="religion">';
echo '<option>--Select--</option>';
echo '<option>Muslim</option>';
echo '<option>Jewish</option>';
echo '<option>Christian</option>';
echo '<option>Pagan</option>';
echo '<option>Sikh</option>';
echo '<option>Hindu</option>';
echo '<option>Buddhist</option>';
echo '<option>Rastafari</option>';
echo '<option>Baha`i</option>';
echo '<option>Shinto</option>';
echo '<option>Event</option>';
echo '</select>';
echo '<div class="demo">';
echo 'From date<input type="text" id="datepicker" name="from_date" value="" /><br/>';
echo '</div>';
echo '<div class="demo">';
echo 'To date<input type="text" id="datepicker2" name="to_date" value="" /><br/>';
echo '</div>';
echo 'Event Details<textarea name="details" rows="4" cols="20">';
echo '</textarea><br/>';
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

?>
