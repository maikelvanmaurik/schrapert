<?php
die("OK!");
header("HTTP/1.1 200 OK");
ob_implicit_flush(true);
$chunks = intval(array_key_exists('chunks', $_GET) ? $_GET['chunks'] : 10);
ob_flush();
$index = 0;
do {
    print "Chunk {$index}. ";
    ob_flush();
    usleep(50000);
} while($index++ < $chunks);