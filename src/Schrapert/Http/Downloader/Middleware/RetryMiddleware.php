<?php

namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Crawl\Exception\DropRequestException;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Log\LoggerInterface;

class RetryMiddleware implements DownloadMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    private $maxRetries;

    private $httpRetryCodes;

    private $logger;

    private static $reasons = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    public function __construct(LoggerInterface $logger, $retries = 2, array $httpRetryCodes = [500, 502, 503, 504, 408])
    {
        $this->logger = $logger;
        $this->maxRetries = $retries;
        $this->httpRetryCodes = $httpRetryCodes;
    }

    public function getHttpRetryCodes()
    {
        return $this->httpRetryCodes;
    }

    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    public function withMaxRetries($retries)
    {
        $new = clone $this;
        $new->maxRetries = intval($retries);

        return $new;
    }

    private function getStatusCodeMessage($code)
    {
        return trim(sprintf('%s %s', $code, self::$reasons[$code] ?: ''));
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        if (! filter_var($request->getMetadata('retry', true), FILTER_VALIDATE_BOOLEAN)) {
            return $response;
        }
        if (in_array($response->getStatusCode(), $this->getHttpRetryCodes())) {
            $reason = $this->getStatusCodeMessage($response->getStatusCode());

            return $this->retry($request, $reason) ?: $response;
        }

        return $response;
    }

    /**
     * @param RequestInterface $request
     * @param $reason
     * @return RequestInterface
     */
    private function retry(RequestInterface $request, $reason)
    {
        $timesRetried = $request->getMetadata('retried', 0);
        if ($timesRetried <= $this->getMaxRetries()) {
            $this->logger->debug('Retrying request {uri} (failed {retries} times): {reason}', [
                'uri' => $request->getUri(),
                'retries' => $timesRetried,
                'reason' => $reason,
            ]);

            return $request->withMetadata('retried', ++$timesRetried);
        }
        $this->logger->debug('Gave up request {uri} tried {retries}, reason: {reason}', [
            'uri' => $request->getUri(),
            'retries' => $timesRetried,
            'reason' => $reason,
        ]);
        throw new DropRequestException();
    }
}
