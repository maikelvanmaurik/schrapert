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
        if(null !== $this->maxMemorySize && count($this->fingerprints) + 1 > $this->maxMemorySize) {
            ksort($this->fingerprints);
            foreach($this->fingerprints as $k => $v) {
                if(count($this->fingerprints) + 1 < $this->maxMemorySize) {
                    break;
                }
                unset($this->fingerprints[$k]);
            }
        }
        list($usec, $sec) = explode(" ", microtime());
        $this->fingerprints[$sec . ' ' . $usec] = $fp;
    }

    public function isDuplicateRequest(RequestInterface $request)
    {
        $deferred = new Deferred();

        $fingerprint = $this->fingerprintGenerator->fingerprint($request);

        // Check the memory first
        if(isset($this->fingerprints[$fingerprint])) {
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