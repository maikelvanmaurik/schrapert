<?php
namespace Schrapert\IO;

use React\Promise\PromiseInterface;

interface FileSystemClientInterface
{
    /**
     * @param $file
     * @return PromiseInterface
     */
    public function readFile($file);

    public function readFileSync($file);

    /**
     * @param $file
     * @param $data
     * @param array $options
     * @return PromiseInterface
     */
    public function writeFile($file, $data, array $options = []);

    public function writeFileSync($file, $data, array $options = []);

    /**
     * @param $directory
     * @return PromiseInterface
     */
    public function readDir($directory);

    /**
     * @param $file
     * @return PromiseInterface
     */
    public function deleteFile($file);

    public function readDirSync($directory);

    public function directoryExists($directory);

    public function createDirectory($path);

    public function stat($path);
}