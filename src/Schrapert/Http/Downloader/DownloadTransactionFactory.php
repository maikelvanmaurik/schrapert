<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Http\RequestDispatcherInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\StreamFactoryInterface;
use Schrapert\Http\UriResolverInterface;

class DownloadTransactionFactory implements DownloadTransactionFactoryInterface
{
    private $dispatcher;

    private $uriResolver;

    private $streamFactory;

    public function __construct(RequestDispatcherInterface $dispatcher, UriResolverInterface $uriResolver, StreamFactoryInterface $streamFactory)
    {
        $this->dispatcher = $dispatcher;
        $this->uriResolver = $uriResolver;
        $this->streamFactory = $streamFactory;
    }

    public function createTransaction(RequestInterface $request)
    {
        return new DownloadTransaction($this->dispatcher, $this->uriResolver, $this->streamFactory);
    }
}