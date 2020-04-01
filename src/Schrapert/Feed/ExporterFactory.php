<?php
namespace Schrapert\Feed;

class ExporterFactory implements ExporterFactoryInterface
{
    private $types;

    private $storageFactory;

    public function __construct(StorageFactoryInterface $storageFactory)
    {
        $this->storageFactory = $storageFactory;
        $this->registerDefaults();
    }

    public function register($format, callable $callback)
    {
        $this->types[$format] = $callback;
    }

    private function registerDefaults()
    {
        $this->register('json', function($uri, $fields, $encoding) {
            return new JsonItemExporter($this->storageFactory->createStorage($uri), $fields, $encoding);
        });
        $this->register('jsonlines', function($uri, $fields, $encoding) {
            return new JsonLinesItemExporter($this->storageFactory->createStorage($uri), $fields, $encoding);
        });
        $this->register('xml', function($uri, $fields, $encoding) {
            return new XmlItemExporter($this->storageFactory->createStorage($uri), $fields, $encoding);
        });
        $this->register('csv', function($uri, $fields, $encoding) {
            return new CsvItemExporter($this->storageFactory->createStorage($uri), $fields, $encoding);
        });
    }

    /**
     * @param $format
     * @param $uri
     * @param $fields
     * @param $encoding
     * @return ExporterInterface
     */
    public function createExporter($format, $uri, array $fields = [], $encoding = 'utf-8')
    {
        if(!array_key_exists($format, $this->types)) {
            throw new \RuntimeException(sprintf("Unknown format '%s'", $format));
        }
        return call_user_func($this->types[$format], $uri, $fields, $encoding);
    }
}