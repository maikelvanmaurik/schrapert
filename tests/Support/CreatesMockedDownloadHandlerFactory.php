<?php

namespace Schrapert\Tests\Support;

use Mockery as m;
use React\Promise\PromiseInterface;
use Schrapert\Downloading\RequestOptionsInterface;
use Schrapert\Downloading\RequestInterface;
use Schrapert\Downloading\HandlerFactoryInterface;
use Schrapert\Downloading\HandlerInterface;

use function React\Promise\resolve;

trait CreatesMockedDownloadHandlerFactory
{
    public function createMockedDownloadHandlerFactoryWithResponseCallback(callable $fn)
    {
        $m = m::mock(HandlerFactoryInterface::class);
        $m->shouldReceive('createHandler')->andReturnUsing(function (RequestInterface $request) use ($fn) {
            return new class($fn, $request) implements HandlerInterface {

                private $fn;

                private $request;

                public function __construct($fn, $request)
                {
                    $this->fn = $fn;
                    $this->request = $request;
                }

                public function download(RequestInterface $request, ?RequestOptionsInterface $options = null) : PromiseInterface
                {
                    return resolve(call_user_func($this->fn, $this->request, $options));
                }
            };
        });
        return $m;
    }
}
