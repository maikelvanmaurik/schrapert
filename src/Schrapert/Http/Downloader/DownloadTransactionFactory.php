<?php
namespace Schrapert\Http\Downloader;

use React\EventLoop\LoopInterface;
use Schrapert\Http\RequestDispatcherInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\StreamFactoryInterface;
use Schrapert\Http\UriResolverInterface;

class DownloadTransactionFactory implements DownloadTransactionFactoryInterface
{
    private $dispatcher;

    private $uriResolver;

    private $streamFactory;

    private $loop;

    public function __construct(LoopInterface $loop, RequestDispatcherInterface $dispatcher, UriResolverInterface $uriResolver, StreamFactoryInterface $streamFactory)
    {
        $this->loop = $loop;
        $this->dispatcher = $dispatcher;
        $this->uriResolver = $uriResolver;
        $this->streamFactory = $streamFactory;
    }

    public function createTransaction(RequestInterface $request)
    {
        return new DownloadTransaction($this->loop, $this->dispatcher, $this->uriResolver, $this->streamFactory);
    }
}