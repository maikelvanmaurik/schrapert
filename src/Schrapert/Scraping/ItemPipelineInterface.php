<?php

namespace Schrapert\Scraping;

use React\Promise\PromiseInterface;

interface ItemPipelineInterface
{
    /**
     * @param ItemInterface $item
     * @return PromiseInterface
     */
    public function processItem(ItemInterface $item);
}
