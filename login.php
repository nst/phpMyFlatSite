<?php

include "include/logic.php";
include "include/config.php";

if($_SERVER['REQUEST_METHOD'] == "POST") {
    if($_POST['user']===$GLOBALS['login'] && $_POST['password']===$GLOBALS['password']) {
		$_SESSION['is_logged_in'] = True;
		$next = isset($_SESSION['call_from_page']) ? $_SESSION['call_from_page'] : 'blog.php';
		header('Location: '.$next);
	}
} else {
    $_SESSION['call_from_page'] = $_SERVER["HTTP_REFERER"];
}

echo build_text_page('login');

?>
