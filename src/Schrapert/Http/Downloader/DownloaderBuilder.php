<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Configuration\ConfigurationInterface;
use Schrapert\Http\ClientFactory;
use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareFactory;
use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareInterface;
use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareManager;
use Schrapert\Http\Downloader\Middleware\RobotsTxtDownloadMiddleware;
use Schrapert\Http\RobotsTxt\Parser;
use Schrapert\Log\LoggerInterface;

class DownloaderBuilder implements DownloaderBuilderInterface
{
    private $clientFactory;

    private $downloadRequestFactory;

    private $logger;

    private $config;

    private $downloadMiddlewareManager;

    private $downloadMiddlewareFactory;

    public function __construct()
    {

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

    public function getDownloadMiddlewareFactory()
    {
        if(null == $this->downloadMiddlewareFactory) {

            $factory = new DownloadMiddlewareFactory();

            $this->downloadMiddlewareFactory = $factory;

            $factory->register('Schrapert\Http\Downloader\Middleware\RobotsTxtDownloadMiddleware', function(DownloaderInterface $downloader) {
                return new RobotsTxtDownloadMiddleware($downloader, new Parser(), $this->getLogger(), $this->getConfiguration()->getSetting('USER_AGENT'));
            });
        }
        return $this->downloadMiddlewareFactory;
    }

    private function getDownloadMiddlewareManager()
    {
        if(null == $this->downloadMiddlewareManager) {
            $this->downloadMiddlewareManager = new DownloadMiddlewareManager([], $this->getLogger());
        }
        return $this->downloadMiddlewareManager;
    }

    public function build()
    {
        $middlewareManager = $this->getDownloadMiddlewareManager();
        $downloader = new Downloader($this->getLogger(), $middlewareManager, $this->downloadRequestFactory);

        // Add the middleware
        $types = $this->getConfiguration()->getSetting('HTTP_DOWNLOAD_DECORATORS', []);
        asort($types, SORT_NUMERIC);
        foreach(array_keys($types) as $type) {
            $middleware = $this->getDownloadMiddlewareFactory()->factory($type, $downloader);

            if(!$middleware instanceof DownloadMiddlewareInterface) {
                throw new \RuntimeException(sprintf("Invalid middleware returned for type '%s'", $type));
            }

            $middlewareManager->addMiddleware($middleware);
        }

        return $downloader;
    }
}