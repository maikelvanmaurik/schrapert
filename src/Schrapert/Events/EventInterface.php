<?php
namespace Schrapert\Event;

interface EventInterface
{
    public function isPropagationStopped();

    public function stopPropagation();

    public function getName();
}