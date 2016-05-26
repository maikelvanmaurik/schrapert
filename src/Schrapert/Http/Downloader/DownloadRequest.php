<?php

namespace Schrapert\Http\Downloader;

use Evenement\EventEmitterTrait;
use GuzzleHttp\Psr7 as gPsr;
use React\SocketClient\ConnectorInterface;
use React\Stream\WritableStreamInterface;
use Schrapert\Http\RequestInterface;

/**
 * @event headers-written
 * @event response
 * @event drain
 * @event error
 * @event end
 */
class DownloadRequest implements WritableStreamInterface
{
    use EventEmitterTrait;

    const STATE_INIT = 0;
    const STATE_WRITING_HEAD = 1;
    const STATE_HEAD_WRITTEN = 2;
    const STATE_END = 3;

    private $connector;

    private $stream;
    private $buffer;
    private $responseFactory;
    private $response;
    private $state = self::STATE_INIT;

    private $pendingWrites = array();

    public function __construct(RequestInterface $request, ConnectorInterface $connector)
    {
        $this->request = $request;
        $this->connector = $connector;
    }

    public function isWritable()
    {
        return self::STATE_END > $this->state;
    }

    public function getUri()
    {
        return $this->request->getUri();
    }

    public function setHeader($name, $value)
    {
        if (self::STATE_WRITING_HEAD <= $this->state) {
            throw new \LogicException('Headers already written');
        }

        $this->requestData->setHeader($name, $value);
    }

    public function writeHead()
    {
        if (self::STATE_WRITING_HEAD <= $this->state) {
            throw new \LogicException('Headers already written');
        }

        $this->state = self::STATE_WRITING_HEAD;

        $streamRef = &$this->stream;
        $stateRef = &$this->state;

        $this
            ->connect()
            ->done(
                function ($stream) use (&$streamRef, &$stateRef) {

                    $streamRef = $stream;

                    $stream->on('drain', array($this, 'handleDrain'));
                    $stream->on('data', array($this, 'handleData'));
                    $stream->on('end', array($this, 'handleEnd'));
                    $stream->on('error', array($this, 'handleError'));

                    $headers = $this->headerString();

                    $stream->write($headers);

                    $stateRef = self::STATE_HEAD_WRITTEN;

                    $this->emit('headers-written', array($this));
                },
                array($this, 'handleError')
            );
    }

    public function write($data)
    {
        if (!$this->isWritable()) {
            return;
        }

        if (self::STATE_HEAD_WRITTEN <= $this->state) {
            return $this->stream->write($data);
        }

        if (!count($this->pendingWrites)) {
            $this->on('headers-written', function ($that) {
                foreach ($that->pendingWrites as $pw) {
                    $that->write($pw);
                }
                $that->pendingWrites = array();
                $that->emit('drain', array($that));
            });
        }

        $this->pendingWrites[] = $data;

        if (self::STATE_WRITING_HEAD > $this->state) {
            $this->writeHead();
        }

        return false;
    }

    public function end($data = null)
    {
        if (null !== $data && !is_scalar($data)) {
            throw new \InvalidArgumentException('$data must be null or scalar');
        }

        if (null !== $data) {
            $this->write($data);
        } else if (self::STATE_WRITING_HEAD > $this->state) {
            $this->writeHead();
        }
    }

    public function handleDrain()
    {
        $this->emit('drain', array($this));
    }

    public function handleData($data)
    {
        $this->buffer .= $data;

        if (false !== strpos($this->buffer, "\r\n\r\n")) {
            list($response, $bodyChunk) = $this->parseResponse($this->buffer);

            $this->buffer = null;

            $this->stream->removeListener('drain', array($this, 'handleDrain'));
            $this->stream->removeListener('data', array($this, 'handleData'));
            $this->stream->removeListener('end', array($this, 'handleEnd'));
            $this->stream->removeListener('error', array($this, 'handleError'));

            $this->response = $response;

            $response->on('end', function () {
                $this->close();
            });
            $response->on('error', function (\Exception $error) {
                $this->closeError(new \RuntimeException(
                    "An error occured in the response",
                    0,
                    $error
                ));
            });

            $this->emit('response', array($response, $this));

            $response->emit('data', array($bodyChunk, $response));
        }
    }

    public function handleEnd()
    {
        $this->closeError(new \RuntimeException(
            "Connection closed before receiving response"
        ));
    }

    public function handleError($error)
    {
        $this->closeError(new \RuntimeException(
            "An error occurred in the underlying stream",
            0,
            $error
        ));
    }

    public function closeError(\Exception $error)
    {
        if (self::STATE_END <= $this->state) {
            return;
        }
        $this->emit('error', array($error, $this));
        $this->close($error);
    }

    public function close(\Exception $error = null)
    {
        if (self::STATE_END <= $this->state) {
            return;
        }

        $this->state = self::STATE_END;

        if ($this->stream) {
            $this->stream->close();
        }

        $this->emit('end', array($error, $this->response, $this));
        $this->removeAllListeners();
    }

    protected function parseResponse($data)
    {
        $psrResponse = gPsr\parse_response($data);
        $headers = array_map(function($val) {
            if (1 === count($val)) {
                $val = $val[0];
            }

            return $val;
        }, $psrResponse->getHeaders());

        $factory = $this->getResponseFactory();

        $response = $factory(
            $this,
            $this->stream,
            'HTTP',
            $psrResponse->getProtocolVersion(),
            $psrResponse->getStatusCode(),
            $psrResponse->getReasonPhrase(),
            $headers
        );

        return array($response, $psrResponse->getBody());
    }

    private function mergeDefaultHeaders(array $headers)
    {
        if(null === $this->request->getPort()) {
            $port = '';
        } elseif($this->getDefaultPort($this->request->getProtocol()) === $this->request->getPort()) {
            $port = ":{$this->request->getPort()}";
        }
        $connectionHeaders = ('1.1' === $this->request->getProtocolVersion()) ? array('Connection' => 'close') : array();
        // $authHeaders = $this->getAuthHeaders();

        return array_merge(
            array(
                'Host'          => $this->request->getHost().$port,
                'User-Agent'    => 'React/alpha'
            ),
            $connectionHeaders,
            // $authHeaders,
            $headers
        );
    }


    private function headerString()
    {
        $headers = $this->mergeDefaultHeaders($this->request->getHeaders());

        $data = '';
        $path = $this->request->getPath();
        if(null != ($qs = $this->request->getQueryString())) {
            $path .= '?'. $qs;
        }
        $data .= "{$this->request->getMethod()} {$path} HTTP/{$this->request->getProtocolVersion()}\r\n";
        foreach ($headers as $name => $value) {
            $data .= "$name: $value\r\n";
        }
        $data .= "\r\n";

        return $data;
    }

    private function getDefaultPort($protocol)
    {
        $defaults = ['http' => 80, 'https' => 443];

        return isset($defaults[$protocol]) ? $defaults[$protocol] : null;
    }

    /**
     * @return \React\Promise\Promise
     */
    protected function connect()
    {
        $host = $this->request->getHost();
        $port = $this->request->getPort();

        if(null === $port) {
            $port = $this->getDefaultPort($this->request->getProtocol());
        }

        $result = $this->connector
            ->create($host, $port);

        return $result;
    }

    public function setResponseFactory($factory)
    {
        $this->responseFactory = $factory;
    }

    public function getResponseFactory()
    {
        if (null === $factory = $this->responseFactory) {

            $factory = function ($request, $stream, $protocol, $version, $code, $reasonPhrase, $headers) {
                return new DownloadResponse(
                    $request,
                    $stream,
                    $protocol,
                    $version,
                    $code,
                    $reasonPhrase,
                    $headers
                );
            };

            $this->responseFactory = $factory;
        }

        return $factory;
    }
}