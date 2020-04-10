<?php

namespace Schrapert\Filter;

use React\Promise\Deferred;
use Schrapert\Crawl\RequestFingerprintGeneratorInterface;
use Schrapert\Crawl\RequestInterface;
use Schrapert\SpiderInterface;

class DuplicateFingerprintRequestFilter implements DuplicateRequestFilterInterface
{
    private $path;

    private $maxMemorySize;

    private $fingerprints;

    private $fingerprintGenerator;

    public function __construct(RequestFingerprintGeneratorInterface $fingerprintGenerator, $path = null, $maxMemorySize = 1000000)
    {
        $this->fingerprintGenerator = $fingerprintGenerator;
        $this->path = $path;
        $this->maxMemorySize = $maxMemorySize;
        $this->fingerprints = [];
    }

    private function push($fp)
    {
        $this->fingerprints[] = $fp;
        if (null !== $this->maxMemorySize && count($this->fingerprints) >= $this->maxMemorySize) {
            $this->fingerprints = array_slice($this->fingerprints, max(1000, ceil($this->maxMemorySize, 4)));
        }
    }

    public function isDuplicateRequest(RequestInterface $request)
    {
        $deferred = new Deferred();

        $fingerprint = $this->fingerprintGenerator->fingerprint($request);

        // Check the memory first
        if (in_array($fingerprint, $this->fingerprints)) {
            $deferred->resolve(true);
        } else {
            // We need to read the fingerprint file
            $deferred->resolve(false);
        }

        $this->push($fingerprint);

        return $deferred->promise();
    }

    public function open(SpiderInterface $spider)
    {
    }
}
