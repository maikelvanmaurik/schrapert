<?php
namespace Schrapert\Feed;

use Schrapert\Scraping\ItemInterface;
use Schrapert\SpiderInterface;
use Schrapert\IO\StreamInterface;

class JsonItemExporter extends AbstractExporter
{
    /**
     * @var StreamInterface
     */
    private $stream;

    private $firstItem;

    public function startExporting(SpiderInterface $spider)
    {
        $this->firstItem = true;
        $this->stream = $this->getStorage()->open();
        $this->stream->write('[');
    }

    public function finishExporting(SpiderInterface $spider)
    {
        $this->stream->write(']');
        return $this->getStorage()->close($this->stream);
    }

    public function exportItem(SpiderInterface $spider, ItemInterface $item)
    {
        if($this->firstItem) {
            $this->firstItem = false;
        } else {
            $this->stream->write("\n,");
        }
        $data = $this->getSerializedFields($item);
        $this->stream->write(json_encode($data));
    }
}