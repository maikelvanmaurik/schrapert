<?php
namespace Schrapert\Schedule;

use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use Schrapert\Crawl\RequestInterface;
use Schrapert\IO\FileSystemClientInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;

class DiskQueue implements PriorityQueueInterface
{
    private $fs;

    private $baseDir;

    private $queue;

    private $count;

    private $memorySize;

    private $logger;

    public function __construct(LoggerInterface $logger, FileSystemClientInterface $fs, $baseDir = null, $memorySize = 1000)
    {
        $this->logger = $logger;
        $this->setBaseDirectory($baseDir);
        $this->fs = $fs;
        $this->memorySize = $memorySize;
        $this->count = 0;
    }

    public function setBaseDirectory($dir)
    {
        if(null === $dir) {
            $this->baseDir = null;
        } else {
            $this->baseDir = rtrim($dir, '/\\');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    public function open(SpiderInterface $spider)
    {

    }

    public function close(SpiderInterface $spider)
    {

    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function push(RequestInterface $request)
    {
        $this->logger->debug("Add {uri} to the disk queue", ['uri' => $request->getUri()]);
        $this->count++;

        if($this->count < $this->memorySize) {
            $this->queue[] = $request;
            return new FulfilledPromise(true);
        }

        throw new \Exception("WRITE TO FILE");
    }

    /**
     * @return PromiseInterface request when there a still requests inside the queue; otherwise, false.
     */
    public function pop()
    {
        if(!empty($this->queue)) {
            $this->count--;
            return new FulfilledPromise(array_pop($this->queue));
        }

        return new RejectedPromise("Empty");

        //TODO read from disk

        $this->logger->debug("Disk queue is empty");

        $file = $this->baseDir.'/active.json';

        return $this->fs->readFile($file)->then(function($content) {

            $this->logger->debug("File contents {content}", ['content' => var_export($content, true)]);

            $requests = json_decode($content, true);

            foreach($requests as $request) {
                $this->push(unserialize($request));
            }

            if($this->count > 0) {
                return $this->pop();
            }


            throw new \Exception("Empty");
        });
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->count;
    }
}