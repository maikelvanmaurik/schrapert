<?php
namespace Schrapert\Feed;

use Schrapert\Scraping\ItemInterface;
use Schrapert\SpiderInterface;
use Schrapert\IO\StreamInterface;

class JsonLinesItemExporter extends AbstractExporter
{
    /**
     * @var StreamInterface
     */
    private $stream;

    public function startExporting(SpiderInterface $spider)
    {
        $this->stream = $this->getStorage()->open();
    }

    public function finishExporting(SpiderInterface $spider)
    {
        return $this->getStorage()->close($this->stream);
    }

    public function exportItem(SpiderInterface $spider, ItemInterface $item)
    {
        $data = $this->getSerializedFields($item);
        $this->stream->write(json_encode($data)."\n");
    }
}