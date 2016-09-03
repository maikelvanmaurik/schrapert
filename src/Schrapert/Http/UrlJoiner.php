<?php
namespace Schrapert\Http;

class UrlJoiner implements UrlJoinerInterface
{
    public function join($uri, $baseUri)
    {   /* return if already absolute URL */
        if (parse_url($uri, PHP_URL_SCHEME) != '' || substr($uri, 0, 2) == '//') {
            return $uri;
        }

        /* queries and anchors */
        if ($uri[0] == '#' || $uri[0] == '?') return $baseUri . $uri;

        /* parse base URL and convert to local variables:
         $scheme, $host, $path */
        $data = parse_url($baseUri);

        if(!array_key_exists('path', $data)) {
            $data['path'] = '';
        }

        /* remove non-directory element from path */
        $data['path'] = preg_replace('#/[^/]*$#', '', $data['path']);

        /* destroy path if relative url points to root */
        if ($uri[0] == '/') {
            $data['path'] = '';
        }

        /* dirty absolute URL */
        $abs = "$data[host]$data[path]/$uri";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        /* absolute URL is ready! */
        return "$data[scheme]://$abs";
    }
}