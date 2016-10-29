<?php
$delay = $_GET['delay'] ?: 10;

printf("DELAY FOR %s SECONDS... ", $delay);
sleep($delay);

die("DONE!");