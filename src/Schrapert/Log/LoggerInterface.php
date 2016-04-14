<?php
namespace Schrapert\Log;

interface LoggerInterface
{
    public function info($message, array $args = []);

    public function warning($message, array $args = []);

    public function error($message, array $args = []);

    public function debug($message, array $args = []);
}