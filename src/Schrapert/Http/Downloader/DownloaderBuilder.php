<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Configuration\ConfigurationInterface;
use Schrapert\Http\Downloader\Decorator\UserAgentDownloadDecorator;
use Schrapert\Http\ClientFactory;
use Schrapert\Http\Downloader\Decorator\RobotsTxtDownloadDecorator;
use Schrapert\Log\LoggerInterface;
use Schrapert\Http\RobotsTxt\Parser as RobotsTxtParser;

class DownloaderBuilder implements DownloaderBuilderInterface
{
    private $clientFactory;

    private $downloadRequestFactory;

    private $logger;

    private $config;

    private $decorators = [];

    public function __construct()
    {
        $this->registerDecorator('Schrapert\Http\Downloader\Decorator\RobotsTxtDownloadDecorator', function(DownloaderInterface $downloader) {
            return new RobotsTxtDownloadDecorator($downloader, new RobotsTxtParser(), $this->getLogger(), $this->getConfiguration()->getSetting('USER_AGENT'));
        });
        $this->registerDecorator('Schrapert\Http\Downloader\Decorator\UserAgentDownloadDecorator', function(DownloaderInterface $downloader) {
           return new UserAgentDownloadDecorator($downloader, $this->getConfiguration()->getSetting('USER_AGENT'));
        });
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    public function setConfiguration(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    public function setHttpClientFactory(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function setDownloadRequestFactory(DownloadRequestFactory $downloadRequestFactory)
    {
        $this->downloadRequestFactory = $downloadRequestFactory;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function registerDecorator($type, callable $fn)
    {
        $this->decorators[$type] = $fn;
    }

    private function decorate(DownloaderInterface $downloader)
    {
        $decorators = $this->getConfiguration()->getSetting('HTTP_DOWNLOAD_DECORATORS', []);
        asort($decorators, SORT_NUMERIC);

        foreach(array_keys($decorators) as $type) {
            if(!empty($this->decorators[$type]) && is_callable($this->decorators[$type])) {
                $downloader = call_user_func($this->decorators[$type], $downloader);
            }
        }

        return $downloader;
    }

    public function build()
    {
        return $this->decorate(new Downloader($this->logger, $this->clientFactory, $this->downloadRequestFactory));
    }
}