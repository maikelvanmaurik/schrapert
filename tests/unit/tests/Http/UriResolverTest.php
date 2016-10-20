<?php
namespace Schrapert\Test\Unit;

use Schrapert\Http\Uri;
use Schrapert\Http\UriResolverInterface;

class UriResolverTest extends \Schrapert\Test\Unit\TestCase
{
    /**
     * @var UriResolverInterface
     */
    private $resolver;

    public function setUp()
    {
        $this->resolver = $this->getContainer()->get('uri_resolver');
    }

    public function testResolveRelativeUrls()
    {
        $str = 'https://user:pass@example.com:1337/some/test/path?q=abc#fragment';
        $base = new Uri($str);

        $new = $this->resolver->resolve($base, '../other-path');

        $this->assertEquals('https://user:pass@example.com:1337/some/other-path', (string)$new);

        $new = $this->resolver->resolve($base, '../other-path/');

        $this->assertEquals('https://user:pass@example.com:1337/some/other-path/', (string)$new);
    }
}