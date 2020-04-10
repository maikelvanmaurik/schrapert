<?php

$value = urldecode($_GET['header-value']);

header("Cache-Control: {$value}");

printf('CURRENT TIME IS: %s', date('Y-m-d H:i:s'));
exit;
