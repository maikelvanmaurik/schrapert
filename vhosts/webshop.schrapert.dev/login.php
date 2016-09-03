<?php
ob_start();
if($_SERVER['REQUEST_METHOD'] == 'POST') {



} else {
    die('v');
}

$content = ob_get_clean();
include 'layout.php';
