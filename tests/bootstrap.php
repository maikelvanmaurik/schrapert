<?php
//require_once rtrim(getenv('COMPOSER_VENDOR_DIR') ?: __DIR__ . '/../vendor', '/\\') . '/autoload.php';
require_once __DIR__.'/../vendor/autoload.php';

define('SRC_DIR', dirname(__DIR__).'/src/');
define('ETC_DIR', dirname(__DIR__).'/etc/');

spl_autoload_register(function($class) {
    $parts = explode('\\', $class);

    if(reset($parts) == 'Schrapert') {

        if($parts[1] == 'Test') {
            $baseDir = dirname(__FILE__) . '/' . strtolower($parts[2]) . '/classes/';
            $parts = array_slice($parts, 3);
        } else {
            $baseDir = SRC_DIR;
        }
        $file = $baseDir . implode('/', $parts) . '.php';

        if(is_file($file)) {
            require_once $file;
        }
    }

    return class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false);
});

foreach(glob(__DIR__.'/common/functions/*.php') as $file) {
    require_once $file;
}