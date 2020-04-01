<?php
namespace Schrapert\Feed;

interface ExporterFactoryInterface
{
    /**
     * @param $format
     * @param $uri
     * @param $fields
     * @param $encoding
     * @return ExporterInterface
     */
    public function createExporter($format, $uri, array $fields = [], $encoding = 'utf-8');
}