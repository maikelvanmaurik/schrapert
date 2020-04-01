<?php
namespace Schrapert\Feed;

use Schrapert\Scraping\ItemInterface;
use Schrapert\SpiderInterface;

abstract class AbstractExporter implements ExporterInterface
{
    private $encoding;

    private $fieldsToExport;

    private $storage;

    public function __construct(StorageInterface $storage, array $fieldsToExport = [], $encoding = 'utf-8')
    {
        $this->storage = $storage;
        $this->fieldsToExport = $fieldsToExport;
        $this->encoding = $encoding;
    }

    public function withExportFields(array $fields)
    {
        $new = clone $this;
        $new->fieldsToExport = array_unique(array_merge($fields, $new->fieldsToExport));
        return $new;
    }

    public function withExportField($field)
    {
        $new = clone $this;
        $new->fieldsToExport = array_unique(array_merge([$field], $new->fieldsToExport));
        return $new;
    }

    public function withoutExportFields(array $fields)
    {
        $new = clone $this;
        $new->fieldsToExport = array_diff($new->fieldsToExport, $fields);
        return $new;
    }

    public function withoutExportField($field)
    {
        $new = clone $this;
        $new->fieldsToExport = array_diff($new->fieldsToExport, [$field]);
        return $new;
    }

    public function getExportFields()
    {
        return $this->fieldsToExport;
    }

    protected function getSerializedFields(ItemInterface $item)
    {
        $data = [];
        $fields = $this->getExportFields();
        if(empty($fields)) {
            $fields = array_map(function($item) { return $item->getName(); }, $item->getFields());
        }
        foreach($item->getFields() as $itemField) {
            if(in_array($itemField->getName(), $fields)) {
                $data[$itemField->getName()] = $itemField->getValue();
            }
        }
        return $data;
    }

    public function withEncoding($encoding)
    {
        $new = clone $this;
        $new->encoding = $encoding;
        return $new;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}