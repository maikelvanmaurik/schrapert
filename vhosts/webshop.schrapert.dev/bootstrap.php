<?php

define('ROOT', __DIR__.'/');
define('ETC', ROOT.'etc/');

include_once ROOT.'/functions.php';

if (is_file(ETC.'maintenance.json')) {
    $data = json_decode(file_get_contents(ETC.'maintenance.json'), true);
    if (time() < intval($data['until_ts'])) {
        header('HTTP/1.1 503 Service Temporarily Unavailable');
        printf('This site is currently in maintenance, please come back after %s', $data['until']);
        exit;
    } else {
        unlink(ETC.'maintenance.json');
    }
}
