<?php

namespace Schrapert\Scraping;

interface ItemPipelineStageInterface
{
    public function processItem(ItemInterface $item);
}
