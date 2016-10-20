<?php
namespace Schrapert\Http;

use Psr\Http\Message\UriInterface;

class UriResolver implements UriResolverInterface
{
    private $pathNormalizer;

    private $uriFactory;

    public function __construct(UriFactoryInterface $uriFactory, PathNormalizerInterface $pathNormalizer)
    {
        $this->uriFactory = $uriFactory;
        $this->pathNormalizer = $pathNormalizer;
    }

    /**
     * @param UriInterface $base
     * @param $rel
     * @return UriInterface
     */
    public function resolve(UriInterface $base, $rel)
    {
        if ($rel === null || $rel === '') {
            return $base;
        }
        if (!($rel instanceof UriInterface)) {
            $rel = $this->uriFactory->createFromParts(parse_url($rel));
        }
        // Return the relative uri as-is if it has a scheme.
        if ($rel->getScheme()) {
            return $rel->withPath($this->pathNormalizer->normalize($rel->getPath()));
        }
        $relParts = array(
            'scheme'    => $rel->getScheme(),
            'authority' => $rel->getAuthority(),
            'path'      => $rel->getPath(),
            'query'     => $rel->getQuery(),
            'fragment'  => $rel->getFragment()
        );
        $parts = array(
            'scheme'    => $base->getScheme(),
            'authority' => $base->getAuthority(),
            'path'      => $base->getPath(),
            'query'     => $base->getQuery(),
            'fragment'  => $base->getFragment()
        );
        if (!empty($relParts['authority'])) {
            $parts['authority'] = $relParts['authority'];
            $parts['path'] = $this->pathNormalizer->normalize($relParts['path']);
            $parts['query'] = $relParts['query'];
            $parts['fragment'] = $relParts['fragment'];
        } elseif (!empty($relParts['path'])) {
            if (substr($relParts['path'], 0, 1) == '/') {
                $parts['path'] = $this->pathNormalizer->normalize($relParts['path']);
                $parts['query'] = $relParts['query'];
                $parts['fragment'] = $relParts['fragment'];
            } else {
                if (!empty($parts['authority']) && empty($parts['path'])) {
                    $mergedPath = '/';
                } else {
                    $mergedPath = substr($parts['path'], 0, strrpos($parts['path'], '/') + 1);
                }
                $parts['path'] = $this->pathNormalizer->normalize($mergedPath . $relParts['path']);
                $parts['query'] = $relParts['query'];
                $parts['fragment'] = $relParts['fragment'];
            }
        } elseif (!empty($relParts['query'])) {
            $parts['query'] = $relParts['query'];
        } elseif ($relParts['fragment'] != null) {
            $parts['fragment'] = $relParts['fragment'];
        }
        return $this->uriFactory->createFromComponents(
            $parts['scheme'],
            $parts['authority'],
            $parts['path'],
            $parts['query'],
            $parts['fragment']
        );
    }
}