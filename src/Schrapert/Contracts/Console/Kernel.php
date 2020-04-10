<?php
namespace Schrapert\Contracts\Console;

interface Kernel
{
    public function handle($input, $output = null);
}