<?php

namespace Schrapert\Crawl;

/**
 * Represents an object which can create fingerprints for given requests. These fingerprints
 * are used to determine if the requests are already processed.
 */
interface RequestFingerprintGeneratorInterface
{
    public function fingerprint(RequestInterface $request);
}
