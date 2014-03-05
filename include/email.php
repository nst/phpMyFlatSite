<?php

$humancheck = $_POST['humancheck'];
$thename = $_POST['thename'];
$email = $_POST['email'];
$message = $_POST['message'];

if ($humancheck != "4" || $thename == '' || $email == '' || $message == '') {
	header("Location: ../contact.php?message_sent=no");
	return;
}

$headers .= "Mime-Version: 1.0\n";
$headers .= "Content-type: text/plain; charset=iso-8859-1; format=flowed\n";
$headers .= "From: $thename <".$email.">\n";
$headers .= "Reply-To: ".$thename." <$email>\n";
$headers .= "X-Mailer: PHP/seriot.ch";
$headers .= "Date: " . date("r");

$browser = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];

$texte = "$message

-- 
Browser: $browser
IP Address: $ip";

mail($GLOBALS['email'],"Message on ".$GLOBALS['site_name']." from $thename",wordwrap(stripslashes($texte)),"$headers");

header("Location: ../contact.php?message_sent=yes");

?>
