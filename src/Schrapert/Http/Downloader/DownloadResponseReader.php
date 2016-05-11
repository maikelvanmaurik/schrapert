<?php
namespace Schrapert\Http\Downloader;

use React\Promise\Deferred;

class DownloadResponseReader
{
    public function __construct()
    {

    }

    public function readToEnd(DownloadResponse $response, callable $end = null)
    {
        $deferred = new Deferred();

        $body = '';

        $response->on('data', function ($chunk) use (&$body) {
            $body .= (string)$chunk;
        });

        $response->on('end', function ($error, $response) use ($deferred, &$body, $end) {

            if(is_callable($end)) {
                call_user_func($end, $error, $response, $body);
            }

            $result = new DownloadResponseReaderResult($response, $body, $error);

            if ($error) {
                $deferred->reject($result);
            } else {
                // Create a response from the data
                $deferred->resolve($result);
            }
        });

        return $deferred->promise();
    }
}