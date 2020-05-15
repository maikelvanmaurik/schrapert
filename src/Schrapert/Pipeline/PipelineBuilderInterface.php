<?php
namespace Schrapert\Pipeline;

interface PipelineBuilderInterface
{
    /**
     * @param array|\Traversable $pipes
     * @return PipelineInterface
     */
    public function build($pipes = null) : PipelineInterface;
}
