<?php

namespace Schrapert\Http\Cache;

use React\Promise;
use Schrapert\Downloading\RequestFingerprintGeneratorInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\Response;
use Schrapert\Http\ResponseInterface;
use Schrapert\Http\StreamFactoryInterface;

class FileStorage implements StorageInterface
{
    private $fingerprintGenerator;

    private $cacheDirectory;

    private $expirationSeconds;

    private $streamFactory;

    public function __construct(RequestFingerprintGeneratorInterface $fingerprintGenerator, StreamFactoryInterface $streamFactory, $expirationSeconds = null, $cacheDirectory = null)
    {
        if (null === $cacheDirectory) {
            $cacheDirectory = sys_get_temp_dir().'/httpcache';
        }
        $this->streamFactory = $streamFactory;
        $this->expirationSeconds = $expirationSeconds;
        $this->cacheDirectory = $cacheDirectory;
        $this->fingerprintGenerator = $fingerprintGenerator;
    }

    public function withCacheDirectory($dir)
    {
        $new = clone $this;
        $new->cacheDirectory = $dir;
        return $new;
    }

    public function retrieveResponse(RequestInterface $request)
    {
        $meta = $this->readMetaData($request);
        if (! $meta) {
            return;
        }
        $path = $this->getRequestPath($request);
        $body = file_get_contents(implode(DIRECTORY_SEPARATOR, [$path, 'response_body']));
        $headers = unserialize(file_get_contents(implode(DIRECTORY_SEPARATOR, [$path, 'response_headers'])));

        return (new Response(intval($meta['status']), null, $headers, $meta['protocol'], $meta['reason']))
            ->withBody($this->streamFactory->createStream($body));
    }

    private function readMetaData(RequestInterface $request)
    {
        $path = $this->getRequestPath($request);
        $metaFile = implode(DIRECTORY_SEPARATOR, [$path, 'meta']);
        if (! is_file($metaFile)) {
            return;
        }
        $mtime = filemtime($metaFile);
        if (null !== $this->expirationSeconds && (time() - $mtime) > intval($this->expirationSeconds)) {
            return;
        }
        return unserialize(file_get_contents($metaFile));
    }

    private function getRequestPath(RequestInterface $request)
    {
        $fp = $this->fingerprintGenerator->fingerprint($request);
        return implode(DIRECTORY_SEPARATOR, [$this->cacheDirectory, substr($fp, 0, 2), $fp]);
    }

    private function body($body)
    {
        return (string)$body;
    }

    private function rrmdir($dir)
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rrmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function clear()
    {
        $this->rrmdir($this->cacheDirectory);
    }

    public function storeResponse(RequestInterface $request, ResponseInterface $response)
    {
        $path = $this->getRequestPath($request);
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $meta = [
            'url' => (string)$request->getUri(),
            'method' => $request->getMethod(),
            'status' => $response->getStatusCode(),
            'response_url' => $response->getHeaderLine('Location'),
            'protocol' => $response->getProtocolVersion(),
            'reason' => $response->getReasonPhrase(),
            'timestamp' => time()
        ];

        $files = [
            'meta' => serialize($meta),
            'response_headers' => serialize($response->getHeaders()),
            'response_body' => $this->body($response->getBody()),
            'request_headers' => serialize($request->getHeaders()),
            'request_body' => $this->body($request->getBody())
        ];

        foreach ($files as $name => $content) {
            file_put_contents(implode(DIRECTORY_SEPARATOR, [$path, $name]), $content);
        }

        return Promise\resolve();
    }
}
