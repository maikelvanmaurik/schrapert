<?php
namespace Schrapert\Scraping;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class ItemPipeline implements ItemPipelineInterface
{
    private $stages;

    public function __construct()
    {
        $this->stages = [];
    }

    public function withStage(ItemPipelineStageInterface $stage)
    {
        $new = clone $this;
        $new->stages[] = $this;
        return $new;
    }

    /**
     * @return ItemPipelineStageInterface[]
     */
    public function getStages()
    {
        return $this->stages;
    }

    /**
     * @param ItemInterface $item
     * @return PromiseInterface
     */
    public function processItem(ItemInterface $item)
    {
        $deferred = new Deferred();
        $chain = new Deferred();

        $promise = $chain->promise();

        foreach($this->getStages() as $stage) {
            $promise = $promise->then(function($item) use ($stage) {
               return $stage->processItem($item);
            });
        }

        $promise->then(function($item) use ($deferred) {
            $deferred->resolve($item);
        }, function($reason) use ($deferred) {
            $deferred->reject($reason);
        });

        $chain->resolve($item);

        return $deferred->promise();
    }
}