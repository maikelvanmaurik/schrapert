<?php
namespace Schrapert\Crawl;

use Traversable;

class RequestFingerprintGenerator implements RequestFingerprintGeneratorInterface
{
    private function recursiveQuery(array &$query, &$src, $parent = [])
    {
        foreach($query as $k => $v) {
            if(is_array($query[$k]) || $query[$k] instanceof Traversable) {
                ksort($query[$k]);
                $path = $parent;
                $path[] = (string)$k;
                $this->recursiveQuery($query[$k], $src, $path);
            } else {
                $src .= "q:" . (!empty($parent) ? '[' . implode('][', $parent) . '][' . $k . ']' : $k) .  ':' . $v . ";";
            }
        }
    }

    public function fingerprint(RequestInterface $request)
    {
        $uri = $request->getUri();
        $info = parse_url($uri);
        $query = [];
        if(!empty($info['query'])) {
            parse_str($info['query'], $query);
        }
        ksort($query);
        ksort($info);
        $src = '';
        // Remove the query since we sorted it and use the query array
        unset($info['query']);
        foreach($info as $k => $v) {
            $src .= "$k:$v;";
        }
        $this->recursiveQuery($query, $src);
        return sha1($src);
    }
}