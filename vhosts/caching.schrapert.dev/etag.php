<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_WARNING);
$eTag = $_GET['etag'] ?: null;
$cc = $_GET['cc'] ?: [];
$lastModified = $_GET['last-modified'] ?: null;
$ccDirectives = [];

if(null !== $lastModified) {
    header("Last-Modified: {$lastModified}");
}
if(null !== $eTag) {
    header("Etag: {$eTag}");
}

if(!empty($cc)) {
    foreach($cc as $k => $v) {
        if('null' === $v) {
            $directives[] = $k;
        } else {
            $directives[] = sprintf('%s=%s', $k, $v);
        }
    }
    header('Cache-Control: ' . implode(',', $directives));
}

if ((null !== $lastModified && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) ||
    (null !== $eTag && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $eTag)) {
    header("HTTP/1.1 304 Not Modified");
    exit;
}

printf("CURRENT TIME IS: %s", date('Y-m-d H:i:s'));