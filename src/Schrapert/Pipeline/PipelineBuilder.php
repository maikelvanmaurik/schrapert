<?php

namespace Schrapert\Pipeline;

use Schrapert\DI\ContainerInterface;

class PipelineBuilder implements PipelineBuilderInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param  array|\Traversable  $pipes
     * @return PipelineInterface
     */
    public function build($pipes = null) : PipelineInterface
    {
        if ($pipes instanceof \Traversable) {
            $pipes = iterator_to_array($pipes);
        } elseif (null !== $pipes) {
            $pipes = (array)$pipes;
        }
        return new Pipeline(array_map(function ($pipe) {
            if (is_string($pipe)) {
                return $this->container->get($pipe);
            }
            return $pipe;
        }, $pipes ?: []));
    }
}
