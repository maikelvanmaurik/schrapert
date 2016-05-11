Schrapert
=========

Schrapert is a scraping/crawler library which is inspired by scrapy. It makes use of React for various operations such as
downloading requests and writing files.

Example of a simple spider:

```php
namespace Crawl;
use Schrapert\Spider;
use Schrapert\Crawl\ResponseInterface;
use Schrapert\Http\ResponseInterface as HttpResponse;
use Schrapert\Http\Request as HttpRequest;
use DOMDocument;
use DOMXPath;
use DOMElement;
class BlogSpider extends Spider
{    
    public function parse(ResponseInterface $response)
    {
        if(!$response instanceof HttpResponse) {
            return;
        }
        $doc = new DOMDocument('1.0');
        $doc->loadHTML((string)$response->getBody());
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query('//a');
        foreach($nodes as $node) {
            /* @var $node DOMElement */
            $uri = $this->uri->join($node->getAttribute('href'), $response->getUri());
            yield new HttpRequest($uri);
        }
    }
}    
```



