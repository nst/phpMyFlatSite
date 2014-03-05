<?php

include "include/logic.php";

if($_GET['message_sent'] == 'yes') {
    echo build_text_page('contact_sent');
} else if ($_GET['message_sent'] == 'no') {
    echo build_text_page('contact_error');
} else {
    echo build_text_page('contact');
}

?>