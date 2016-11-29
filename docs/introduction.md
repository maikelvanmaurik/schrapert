---
permalink: docs/index.html
---

# What is Schrapert

Schapert is an crawling/scraping project written in PHP which uses a non-blocking approach thanks to [ReactPHP](https://github.com/reactphp/react). The project is heavily
inspired by [Scrapy](http://scrapy.org) (which is written in Python).

When you use Schrapert you need to write a custom spider in the form of a class. These classes can be very basic.
The minimum requirements of a spider are that they provide some url's to start crawling and it should parse the responses
which are returned from the downloader, this is done by the `parse` method. The `parse` method may return 2 values:
A new request or an scraped item.

A very basic example of a spider look like this:

```php
<?php
namespace MyProject;

use DOMDocument;
use DOMXPath;

class QuoteSpider {

    protected $startUri = ['http://www.test.nl'];
    
    public function parse(ResponseInterface $response)
    {
        // Create a new dom document
        $doc = new DOMDocument();
        // Load the html of the response by casting the body of the response to a string
        $doc->loadHTML((string)$response->getBody());
        // Create a xpath object
        $xpath = new DOMXPath($doc);
        // Iterate all the a tags inside the document
        foreach($xpath->query('//a') as $link) {
            // Create new requests of by using the href of the link
            yield new Request($link->getAttribute('href'));
        }
    }
    
}
```

