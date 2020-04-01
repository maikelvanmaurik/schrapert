<?php
namespace Schrapert\Http\Cache;

use Psr\Http\Message\MessageInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use DateTime;

/**
 * Class Rfc2616Policy
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Caching
 * @package Schrapert\Http\Cache
 */
class Rfc2616Policy implements PolicyInterface, RequestProcessorPolicyInterface
{
    /**
     * One year
     * @var int
     */
    const MAX_AGE = 31536000;

    private $ignoreCacheControls = [];

    private $ignoreSchemes;

    private $alwaysStore;

    private $time;

    public function __construct($alwaysStore = false, array $ignoreResponseCacheControls = [], array $ignoreSchemes = ['file'])
    {
        $this->alwaysStore = $alwaysStore;
        $this->ignoreCacheControls = $ignoreResponseCacheControls;
        $this->ignoreSchemes = $ignoreSchemes;
    }

    private function parseCacheControlHeader(MessageInterface $message)
    {
        $directives = array_filter(explode(',', $message->getHeaderLine('Cache-Control')), 'trim');
        foreach ((array)$directives as $index => &$directive) {
            if (strpos($directive, '=')) {
                list($key, $value) = explode('=', $directive, 2);
            } else {
                $key = $directive;
                $value = null;
            }
            unset($directives[$index]);
            $directives[$key] = $value;
        }
        return $directives;
    }

    public function getIgnoreSchemes()
    {
        return $this->ignoreSchemes;
    }

    public function withIgnoreSchemes(array $schemes)
    {
        $new = clone $this;
        $new->ignoreSchemes = array_unique(array_merge($this->ignoreSchemes, $schemes));
        return $new;
    }

    public function withoutIgnoreSchemes(array $schemes)
    {
        $new = clone $this;
        $new->ignoreSchemes = array_diff($this->ignoreSchemes, $schemes);
        return $new;
    }

    public function withAlwaysStore($store)
    {
        $new = clone $this;
        $new->alwaysStore = filter_var($store, FILTER_VALIDATE_BOOLEAN);
        return $new;
    }

    public function withIgnoreResponseCacheControls(array $cacheControls = [])
    {
        $clone = clone $this;
        $clone->ignoreCacheControls = array_unique(array_merge($clone->ignoreCacheControls, $cacheControls));
        return $clone;
    }

    public function isCachedResponseValid(ResponseInterface $cached, ResponseInterface $response, RequestInterface $request)
    {
        // Use the cached response if the new response is a server error,
        // as long as the old response didn't specify must-revalidate.
        if($response->getStatusCode() >= 500) {
            $cc = $this->parseCacheControlHeader($cached);
            if (!array_key_exists('must-revalidate', $cc)) {
                return true;
            }
        }
        // Use the cached response if the server says it hasn't changed.
        return $response->getStatusCode() == 304;
    }

    public function shouldCacheRequest(RequestInterface $request)
    {
        if (in_array($request->getUri()->getScheme(), $this->ignoreSchemes)) {
            return false;
        }
        $cc = $this->parseCacheControlHeader($request);
        if (array_key_exists('no-store', $cc)) {
            return false;
        }
        return true;
    }

    public function shouldCacheResponse(ResponseInterface $response, RequestInterface $request)
    {
        $cc = $this->parseCacheControlHeader($response);
        if (array_key_exists('no-store', $cc)) {
            return false;
        }
        // Never cache 304 (Not Modified) responses
        if ($response->getStatusCode() == 304) {
            return false;
        }
        // Cache unconditionally if configured to do so
        if ($this->alwaysStore) {
            return true;
        }

        // Any hint on response expiration is good
        if (array_key_exists('max-age', $cc) || $response->hasHeader('Expires')) {
            return true;
        }
        // Firefox fall-backs this statuses to one year expiration if none is set
        if (in_array($response->getStatusCode(), [300, 301, 308])) {
            return true;
        }
        // Other statuses without expiration requires at least one validator
        if (in_array($response->getStatusCode(), [200, 203, 401])) {
            return $response->hasHeader('Last-Modified') || $response->hasHeader('ETag');
        }
        // Any other is probably not eligible for caching
        // Makes no sense to cache responses that does not contain expiration
        // info and can not be re-validated
        return false;
    }

    private function getMaxAge(array $directives)
    {
        if (array_key_exists('max-age', $directives)) {
            $value = intval($directives['max-age']);
            return $value < 0 ? null : $value;
        }
        return null;
    }

    private function rfc1123ToEpoch($value)
    {
        $date = date_create_from_format(DateTime::RFC1123, $value);
        if ($date instanceof DateTime) {
            return $date->getTimestamp();
        }
        return null;
    }

    /**
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Caching#Freshness describes how the
     * freshness lifetime is calculated
     *
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @param int $now
     * @return int
     */
    private function computeFreshnessLifetime(ResponseInterface $response, RequestInterface $request, $now)
    {
        $cc = $this->parseCacheControlHeader($response);
        if (null !== ($maxAge = $this->getMaxAge($cc))) {
            return $maxAge;
        }

        if (null === ($date = $this->rfc1123ToEpoch($response->getHeaderLine('Date')))) {
            $date = $now;
        }

        if ($response->hasHeader('Expires')) {
            // When parsing Expires header fails RFC 2616 section 14.21 says we
            // should treat this as an expiration time in the past.
            if (null === ($expires = $this->rfc1123ToEpoch($response->getHeaderLine('Expires')))) {
                return 0;
            }
            return max(0, $expires - $date);
        }

        // Fallback to heuristic using last-modified header
        // This is not in RFC but on Firefox caching implementation
        $lastModified = $this->rfc1123ToEpoch($response->getHeaderLine('Last-Modified'));
        if (null !== $lastModified && $lastModified <= $date) {
            return floor(($date - $lastModified) / 10);
        }

        // This request can be cached indefinitely
        if (in_array($response->getStatusCode(), [300, 301, 308])) {
            return self::MAX_AGE;
        }

        // Insufficient information to compute freshness lifetime
        return 0;
    }

    public function getCurrentTime()
    {
        if(null === ($time = $this->time)) {
            return time();
        }
        return $time;
    }

    public function withCurrentTime($time)
    {
        $new = clone $this;
        $new->time = $time;
        return $new;
    }

    public function isCachedResponseFresh(ResponseInterface $cachedResponse, RequestInterface $request)
    {
        $ccResponse = $this->parseCacheControlHeader($cachedResponse);
        $ccRequest = $this->parseCacheControlHeader($request);

        if (array_key_exists('no-cache', $ccResponse) || array_key_exists('no-cache', $ccRequest)) {
            return false;
        }

        $now = $this->getCurrentTime();
        $freshnessLifetime = $this->computeFreshnessLifetime($cachedResponse, $request, $now);
        $currentAge = $this->computeCurrentAge($cachedResponse, $request, $now);

        if(null !== ($requestMaxAge = $this->getMaxAge($ccRequest))) {
            if(0 === $freshnessLifetime) { // When there was to little data to compute the freshness lifetime by the response just use the request max age
                $freshnessLifetime = $requestMaxAge;
            } else {
                $freshnessLifetime = min($freshnessLifetime, $requestMaxAge);
            }
        }

        if(array_key_exists('min-fresh', $ccRequest)) {
            $minFreshSeconds = $ccRequest['min-fresh'];
            if($currentAge + $minFreshSeconds > $freshnessLifetime) {
                return false;
            }
        }

        if($currentAge < $freshnessLifetime) {
            return true;
        }

        if(array_key_exists('max-stale', $ccRequest) && !array_key_exists('must-revalidate', $ccResponse)) {
            # From RFC2616: "Indicates that the client is willing to
            # accept a response that has exceeded its expiration time.
            # If max-stale is assigned a value, then the client is
            # willing to accept a response that has exceeded its
            # expiration time by no more than the specified number of
            # seconds. If no value is assigned to max-stale, then the
            # client is willing to accept a stale response of any age."
            $staleAge = $ccRequest['max-stale'];

            if (null === $staleAge) {
                return true;
            }

            if($currentAge < ($freshnessLifetime + max(0, intval($staleAge)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Processes the request allowing a policy to add additional headers etc. before
     * dispatching the request
     *
     * @param ResponseInterface $cachedResponse
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function processRequest(ResponseInterface $cachedResponse, RequestInterface $request)
    {
        if ($cachedResponse->hasHeader('Last-Modified')) {
            $request = $request->withHeader('If-Modified-Since', $cachedResponse->getHeaderLine('Last-Modified'));
        }
        if ($cachedResponse->hasHeader('ETag')) {
            $request = $request->withHeader('If-None-Match', $cachedResponse->getHeaderLine('ETag'));
        }
        return $request;
    }

    private function computeCurrentAge(ResponseInterface $response, RequestInterface $request, $now)
    {
        // Reference nsHttpResponseHead::ComputeCurrentAge
        // http://dxr.mozilla.org/mozilla-central/source/netwerk/protocol/http/nsHttpResponseHead.cpp#366
        $currentAge = 0;
        // If Date header is not set we assume it is a fast connection, and
        // clock is in sync with the server
        $date = $this->rfc1123ToEpoch($response->hasHeader('Date') ? $response->getHeaderLine('Date') : $now);

        if ($now > $date) {
            $currentAge = $now - $date;
        }
        if ($response->hasHeader('Age')) {
            $currentAge = intval($response->getHeaderLine('Age'));
        }
        return $currentAge;
    }
}