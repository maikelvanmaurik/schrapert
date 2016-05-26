<?php
namespace Schrapert\Configuration;

use Schrapert\Log\Logger;

class DefaultConfiguration extends Configuration
{
    public function __construct()
    {
        parent::__construct([
            'LOG_PATH' => sys_get_temp_dir().'/spider.log',
            'LOG_LEVEL' => Logger::ALL,
            'SCHEDULER_DISK_PATH' => sys_get_temp_dir().'/requests',
            'HTTP_DOWNLOAD_MIDDLEWARE' => [
                'Schrapert\Http\Downloader\Middleware\RobotsTxtDownloadMiddleware' => 100
            ]
        ]);
    }
}