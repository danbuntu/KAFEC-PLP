<?php
//Check and Increment Cookie
if(!isset($_COOKIE['Counter'])) {
echo "top section<br />";
$Counter = $_COOKIE['Counter']+1;
setcookie("Counter", $Counter );
echo "Setting Cookie to " . $Counter . "<br />";
}
else {
echo "Cookie not set";
setcookie("Counter", "1" );	
$Counter = 1;
}
?>
<?php
// Print an individual cookie
echo $_COOKIE["Counter"];
echo $HTTP_COOKIE_VARS["Counter"];

// Another way to debug/test is to view all cookies
print_r($_COOKIE);
?>


<html>
<body>

<?php
// Print individual cookies
echo "You have visited " . $Counter . " times ";


?>