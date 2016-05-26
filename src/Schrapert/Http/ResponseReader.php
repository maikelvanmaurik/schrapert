<?php
namespace Schrapert\Http;

use React\Promise\Deferred;

class ResponseReader implements ResponseReaderInterface
{
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function readToEnd()
    {
        $response = $this->response;

        $deferred = new Deferred();

        if($response instanceof ReadableResponseStreamInterface) {

            $body = '';

            $response->on('data', function ($chunk) use (&$body) {
                $body .= (string)$chunk;
            });

            $response->on('end', function ($error, $response) use ($deferred, &$body) {

                $result = new ResponseReaderResult($response, $body, $error);

                if ($error) {
                    $deferred->reject($result);
                } else {
                    // Create a response from the data
                    $deferred->resolve($result);
                }
            });
        } elseif($response instanceof ResponseProvidingBodyInterface) {
            $body = $response->getBody();
            $result = new ResponseReaderResult($response, $body, null);
            $deferred->resolve($result);
        } else {
            $body = (string)$response;
            $deferred->resolve(new ResponseReaderResult($response, $body, null));
        }

        return $deferred->promise();
    }
}