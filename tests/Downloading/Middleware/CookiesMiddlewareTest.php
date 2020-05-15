<?php

namespace Schrapert\Tests\Downloading\Middleware;

use Schrapert\Crawling\Downloader;
use Schrapert\Crawling\Exceptions\IgnoreRequestException;
use Schrapert\DI\ContainerInterface;
use Schrapert\DI\DefaultContainer;
use Schrapert\Downloading\RequestOptions;
use Schrapert\Downloading\Middleware\CookiesMiddleware;
use Schrapert\Downloading\Middleware\RobotsTxtDownloaderMiddleware;
use Schrapert\Downloading\Request;
use Schrapert\Downloading\RequestInterface;
use Schrapert\Downloading\Response;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Http\Cookies\CookieJar;
use Schrapert\Http\Cookies\CookieJarManagerInterface;
use Schrapert\Http\RobotsTxt\Parser;
use Schrapert\Http\Response as HttpResponse;
use Schrapert\Pipeline\PipelineBuilder;
use Schrapert\Tests\Support\CreatesDownloader;
use Schrapert\Tests\Support\CreatesMockedDownloadHandlerFactory;
use Schrapert\Tests\TestCase;

class CookiesMiddlewareTest extends TestCase
{
    use CreatesDownloader;
    use CreatesMockedDownloadHandlerFactory;

    /**
     * @test
     */
    public function its_possible_to_provide_a_cookie_jar_instance()
    {
        $container = new DefaultContainer();

        $downloader = $this->createDownloader(
            null,
            new PipelineBuilder($container),
            $this->createMockedDownloadHandlerFactoryWithResponseCallback(function (RequestInterface $request) {
                return new HttpResponse(200, [
                    'Set-Cookie' => 'foo=bar'
                ], '');
            })
        );

        $container->bind(
            CookiesMiddleware::class,
            function (ContainerInterface $container) use ($downloader) {
                return new CookiesMiddleware(
                    $container[EventDispatcherInterface::class],
                    $container[CookieJarManagerInterface::class]
                );
            }
        );

        $downloader->withMiddleware(CookiesMiddleware::class);
        $jar = new CookieJar();

        $response = await($downloader->download(new Request('http://foo.bar/search'), new RequestOptions([
            'cookiejar' => $jar,
            'cookies' => ['foo' => 'bar']
        ])));

        $cookies = $jar->getCookies('foo.bar');
    }

    /**
     * @test
     */
    public function cookies_are_persisted_per_jar_when_using_the_cookiejar_option()
    {
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
            CookiesMiddleware::class,
            function (ContainerInterface $container) use ($downloader) {
                return new CookiesMiddleware(
                    $container[EventDispatcherInterface::class],
                    $container[CookieJarManagerInterface::class]
                );
            }
        );

        $downloader->withMiddleware(CookiesMiddleware::class);

        $test = await($downloader->download(new Request('http://foo/search'), new RequestOptions([
            'cookiejar' => 'jar1',
            'cookies' => ['foo' => 'bar']
        ])));

        var_dump(get_class($test));
    }
}
