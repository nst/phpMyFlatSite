<?php

include "include/logic.php";

unset($_SESSION['is_logged_in']);
unset($_SESSION['call_from_page']);

session_destroy();

$next_page = $_SERVER["HTTP_REFERER"] == '' ? 'index.php' : $_SERVER["HTTP_REFERER"];

header('Location: '.$next_page);

?>