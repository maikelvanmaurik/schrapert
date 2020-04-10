<?php

namespace Schrapert\Feed;

use Schrapert\Scraping\ItemInterface;
use Schrapert\SpiderInterface;

interface ExporterInterface
{
    public function withExportFields(array $fields);

    public function withExportField($field);

    public function withoutExportFields(array $fields);

    public function withoutExportField($field);

    public function getExportFields();

    public function withEncoding($encoding);

    public function startExporting(SpiderInterface $spider);

    public function finishExporting(SpiderInterface $spider);

    public function exportItem(SpiderInterface $spider, ItemInterface $item);
}
