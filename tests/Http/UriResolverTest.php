<?php

namespace Schrapert\Tests;

use Schrapert\Http\PathNormalizer;
use Schrapert\Http\Uri;
use Schrapert\Http\UriFactory;
use Schrapert\Http\UriResolver;
use Schrapert\Http\UriResolverInterface;

class UriResolverTest extends TestCase
{
    /**
     * @var UriResolverInterface
     */
    private $resolver;

    public function setUp(): void
    {
        $this->resolver = new UriResolver(new UriFactory(), new PathNormalizer());
        parent::setUp();
    }

    public function testResolveRelativeUrls()
    {
        $str = 'https://user:pass@example.com:1337/some/test/path?q=abc#fragment';
        $base = new Uri($str);

        $new = $this->resolver->resolve($base, '../other-path');

        $this->assertEquals('https://user:pass@example.com:1337/some/other-path', (string) $new);

        $new = $this->resolver->resolve($base, '../other-path/');

        $this->assertEquals('https://user:pass@example.com:1337/some/other-path/', (string) $new);
    }
}
