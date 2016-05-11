<?php
require_once rtrim(getenv('COMPOSER_VENDOR_DIR') ?: __DIR__ . '/../vendor', '/\\') . '/autoload.php';

define('SRC_DIR', dirname(__DIR__).'/src/');

spl_autoload_register(function($class) {
    $parts = explode('\\', $class);

    if(reset($parts) == 'Schrapert') {
        $file = SRC_DIR . implode('/', $parts) . '.php';
        if(is_readable($file)) {
            require_once $file;
        }
    }
    return class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false);
});

foreach(glob(__DIR__.'/common/functions/*.php') as $file) {
    require_once $file;
}