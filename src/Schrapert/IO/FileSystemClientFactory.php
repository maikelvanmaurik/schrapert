<?php
namespace Schrapert\IO;

use React\EventLoop\LoopInterface;
use React\Filesystem\Filesystem;

class FileSystemClientFactory
{
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function factory()
    {
        return new FileSystemClient(Filesystem::create($this->loop));
    }
}