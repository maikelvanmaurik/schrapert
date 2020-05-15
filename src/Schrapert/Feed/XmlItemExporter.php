<?php

namespace Schrapert\Feed;

use Schrapert\IO\StreamInterface;
use Schrapert\Scraping\ItemInterface;
use Schrapert\SpiderInterface;

class XmlItemExporter extends AbstractExporter
{
    private $rootTagName;

    private $itemTagName;
    /**
     * @var StreamInterface
     */
    private $stream;

    private $camelcaseCache;

    public function __construct(StorageInterface $storage, array $fields = [], $encoding = 'utf-8', $rootTagName = 'items', $itemTagName = 'item')
    {
        parent::__construct($storage, $fields, $encoding);
        $this->rootTagName = $rootTagName;
        $this->itemTagName = $itemTagName;
        $this->camelcaseCache = [];
    }

    public function getRootTagName()
    {
        return $this->rootTagName;
    }

    public function getItemTagName()
    {
        return $this->itemTagName;
    }

    private function camelcase($str)
    {
        if (isset($this->camelcaseCache[$str])) {
            return $this->camelcaseCache[$str];
        }
        $this->camelcaseCache[$str] = $camelcase = preg_replace_callback('/([\s_\-])([a-z])/', function ($c) {
            return strtoupper($c[2]);
        }, $str);

        return $camelcase;
    }

    public function startExporting(SpiderInterface $spider)
    {
        $this->stream = $this->getStorage()->open();
        $this->stream->write(sprintf("<?xml version=\"%s\" encoding=\"%s\" ?>\n", '1.0', $this->getEncoding()));
        $this->stream->write(sprintf("<%s>\n", $this->getRootTagName()));
    }

    public function finishExporting(SpiderInterface $spider)
    {
        $this->stream->write(sprintf("</%s>\n", $this->getRootTagName()));
        return $this->getStorage()->close($this->stream);
    }

    public function exportItem(SpiderInterface $spider, ItemInterface $item)
    {
        $this->stream->write(sprintf("\t<%s>\n", $this->getItemTagName()));
        $fields = $this->getSerializedFields($item);
        foreach ($fields as $key => $value) {
            $tagName = $this->camelcase($key);
            $this->stream->write(sprintf("\t\t<%s>%s</%s>\n", $tagName, $value, $tagName));
        }
        $this->stream->write(sprintf("\t</%s>\n", $this->getItemTagName()));
    }
}
