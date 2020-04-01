<?php
namespace Schrapert\Http;

class UriFactory implements UriFactoryInterface
{
    public function createFromParts(array $parts)
    {
        $uri = (new Uri())
            ->withScheme(isset($parts['scheme']) ? $parts['scheme'] : '')
            ->withUserInfo(isset($parts['user']) ? $parts['user'] . (isset($parts['pass']) ? (':' . $parts['pass']) : '') : '')
            ->withPort(isset($parts['port']) ? $parts['port'] : null)
            ->withPath(isset($parts['path']) ? $parts['path'] : '')
            ->withQuery(isset($parts['query']) ? $parts['query'] : '');

        return $uri;
    }

    /**
     * @param $scheme
     * @param $authority
     * @param $path
     * @param $query
     * @param $fragment
     * @return UriInterface
     */
    public function createFromComponents($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';
        if (!empty($scheme)) {
            $uri .= $scheme . '://';
        }
        if (!empty($authority)) {
            $uri .= $authority;
        }
        if ($path != null) {
            // Add a leading slash if necessary.
            if ($uri && substr($path, 0, 1) !== '/') {
                $uri .= '/';
            }
            $uri .= $path;
        }
        if ($query != null) {
            $uri .= '?' . $query;
        }
        if ($fragment != null) {
            $uri .= '#' . $fragment;
        }
        return $uri;
    }
}