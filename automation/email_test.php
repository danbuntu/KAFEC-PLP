<?
$headers = 'From: webmaster@midkent.ac.uk';
mail('dan.attwood@midkent.ac.uk', 'Test email using PHP', 'This is a test email message', $headers, '-fwebmaster@example.com');
?>

//<?php
//$to = 'dan.attwood@midkent.ac.uk';
//$subject = 'Test email using PHP';
//$message = 'This is a test email message';
//$headers = 'From: webmaster@example.com' . "\r\n" .
//   'Reply-To: webmaster@example.com' . "\r\n" .
//   'X-Mailer: PHP/' . phpversion();
//
//mail($to, $subject, $message, $headers, '-fwebmaster@example.com');
//?>