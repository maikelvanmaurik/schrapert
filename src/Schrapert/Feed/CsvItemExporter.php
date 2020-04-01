<?php
namespace Schrapert\Feed;

use Schrapert\Scraping\ItemInterface;
use Schrapert\SpiderInterface;
use Schrapert\IO\StreamInterface;

class CsvItemExporter extends AbstractExporter
{
    /**
     * @var StreamInterface
     */
    private $stream;

    private $headersWritten;

    private $includeHeaderLine;

    public function __construct(StorageInterface $storage, array $fields = [], $encoding, $includeHeaderLine=true)
    {
        parent::__construct($storage, $fields, $encoding);
        $this->includeHeaderLine = $includeHeaderLine;

    }

    public function startExporting(SpiderInterface $spider)
    {
        $this->headersWritten = false;
        $this->stream = $this->getStorage()->open();
    }

    public function finishExporting(SpiderInterface $spider)
    {
        return $this->getStorage()->close($this->stream);
    }

    private function writeRow(array $columns)
    {
        $columns = array_map(function($item) {
            return addslashes($item);
        }, $columns);
        $this->stream->write(sprintf("\"%s\"\n", implode('","', $columns)));
    }

    private function writeHeaders(ItemInterface $item)
    {
        $fields = $this->getSerializedFields($item);
        $this->writeRow(array_keys($fields));
    }

    public function exportItem(SpiderInterface $spider, ItemInterface $item)
    {
        if(!$this->headersWritten && $this->includeHeaderLine) {
            $this->writeHeaders($item);
            $this->headersWritten = true;
        }
        $data = $this->getSerializedFields($item);
        $this->writeRow($data);
    }
}