<?php

namespace Schrapert\Http;

class PathNormalizer implements PathNormalizerInterface
{
    private $noopPaths = ['' => true, '/' => true, '*' => true];

    private $ignoreSegments = ['.' => true, '..' => true];

    public function __construct()
    {
    }

    public function normalize($path)
    {
        if (isset($this->noopPaths[$path])) {
            return $path;
        }
        $results = [];
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment == '..') {
                array_pop($results);
            } elseif (! isset($this->ignoreSegments[$segment])) {
                $results[] = $segment;
            }
        }
        $newPath = implode('/', $results);
        // Add the leading slash if necessary
        if (substr($path, 0, 1) === '/' &&
            substr($newPath, 0, 1) !== '/'
        ) {
            $newPath = '/'.$newPath;
        }
        // Add the trailing slash if necessary
        if ($newPath != '/' && isset($this->ignoreSegments[end($segments)])) {
            $newPath .= '/';
        }
        return $newPath;
    }
}
