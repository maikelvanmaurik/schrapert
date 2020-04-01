<?php
namespace Schrapert\Test\Integration;

use PHPUnit_Framework_TestCase;
use Schrapert\DependencyInjection\DefaultServiceContainer;
use Symfony\Component\CssSelector\CssSelectorConverter;
use DOMNode;
use DOMDocument;
use DOMXPath;
use DOMNodeList;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    private $container;

    public function getContainer()
    {
        if(!$this->container) {
            $this->container = new DefaultServiceContainer();
        }
        return $this->container;
    }

    protected function xpath(DOMDocument $doc, $expression, DOMNode $context = null)
    {
        $xpath = new DOMXPath($doc);
        return $xpath->query($expression, $context);
    }

    /**
     * @param DOMDocument $doc
     * @param $selector
     * @param DOMNode $context
     * @return DOMNodeList
     */
    protected function css(DOMDocument $doc, $selector, DOMNode $context = null)
    {
        $converter = new CssSelectorConverter(true);
        $expression = $converter->toXPath($selector);
        $xpath = new DOMXPath($doc);
        return $xpath->query($expression, $context);
    }
}