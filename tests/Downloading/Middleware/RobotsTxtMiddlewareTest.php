<?php

namespace Schrapert\Tests\Downloading\Middleware;

use Schrapert\Crawling\Downloader;
use Schrapert\Crawling\Exceptions\IgnoreRequestException;
use Schrapert\DI\ContainerInterface;
use Schrapert\DI\DefaultContainer;
use Schrapert\Downloading\Middleware\RobotsTxtDownloaderMiddleware;
use Schrapert\Downloading\Request;
use Schrapert\Downloading\RequestInterface;
use Schrapert\Downloading\Response;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Http\RobotsTxt\Parser;
use Schrapert\Pipeline\PipelineBuilder;
use Schrapert\Tests\Support\CreatesDownloader;
use Schrapert\Tests\Support\CreatesMockedDownloadHandlerFactory;
use Schrapert\Tests\TestCase;

class RobotsTxtMiddlewareTest extends TestCase
{
    use CreatesDownloader;
    use CreatesMockedDownloadHandlerFactory;


    /**
     * @test
     */
    public function when_the_robots_txt_specifies_a_wildcard_user_agent_the_rules_should_apply_to_all_user_agents()
    {
        $this->expectException(IgnoreRequestException::class);
        $container = new DefaultContainer();

        $downloader = $this->createDownloader(
            null,
            new PipelineBuilder($container),
            $this->createMockedDownloadHandlerFactoryWithResponseCallback(function (RequestInterface $request) {
                switch ($request->getUri()->getPath()) {
                    case '/robots.txt':
                        return new Response('
User-agent: *
Disallow: /search
Allow: /search/about
                     ');
                        break;
                    default:
                        return new Response('Crawler hidden content');
                }
            })
        );

        $container->bind(
            RobotsTxtDownloaderMiddleware::class,
            function (ContainerInterface $container) use ($downloader) {
                return new RobotsTxtDownloaderMiddleware(
                    $container[EventDispatcherInterface::class],
                    $downloader,
                    $container[Parser::class],
                    null
                );
            }
        );

        $downloader->withMiddleware(RobotsTxtDownloaderMiddleware::class);

        await($downloader->download(new Request('http://foo/search')));
    }
}
