<?php

$seconds = array_key_exists('duration', $_GET) ? $_GET['duration'] : 10;

header("Cache-Control: max-age={$seconds}");

printf('CURRENT TIME IS: '.date('Y-m-d H:i:s'));
exit;
