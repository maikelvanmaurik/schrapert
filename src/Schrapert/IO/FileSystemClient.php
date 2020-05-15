<?php

namespace Schrapert\IO;

use Exception;
use React\Filesystem\FilesystemInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Stream\BufferedSink;
use React\Stream\WritableStreamInterface;

class FileSystemClient implements FileSystemClientInterface
{
    private $fs;

    public function __construct(FilesystemInterface $fs)
    {
        $this->fs = $fs;
    }

    public function readFile($file)
    {
        $deferred = new Deferred();

        $promise = $deferred->promise();

        $f = $this->fs->file($file);

        $f->exists()->then(function () use ($f, $deferred) {
            $f->open('r')->then(function ($stream) use ($f, $deferred) {
                $deferred->resolve(BufferedSink::createPromise($stream)->always(function () {
                    $handle->close();
                }));
            }, function () use ($deferred) {
                $deferred->reject('file does not exist');
            });
        }, function () use ($deferred) {
            $deferred->reject('File does not exist');
        });

        return $promise;
    }

    public function readFileSync($file)
    {
        return file_get_contents($file);
    }

    public function writeFile($file, $data, array $options = [])
    {
        return $this->fs->file($file)->open('cwt')->then(function (WritableStreamInterface $stream) use ($data) {
            try {
                $stream->write((string)$data);
                $stream->end();
            } catch (Exception $e) {
                throw $e;
            }
            return true;
        });
    }

    public function deleteFile($file)
    {
        return $this->fs->file($file)->remove();
    }

    public function writeFileSync($file, $data, array $options = [])
    {
        return file_put_contents($file, $data);
    }

    /**
     * @param $directory
     * @return PromiseInterface
     */
    public function readDir($directory)
    {
        return $this->fs->dir($directory)->ls();
    }

    public function readDirSync($directory)
    {
        $files = scandir($directory);
        return array_filter($files, function ($item) {
            return $item != '.' && $item != '..';
        });
    }

    public function stat($path)
    {
        return $this->fs->stat($path);
    }

    public function directoryExists($directory)
    {
        $this->fs->dir($directory)->stat();
    }

    public function createDirectory($path)
    {
        // TODO: Implement createDirectory() method.
    }
}
