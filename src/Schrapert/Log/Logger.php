<?php
namespace Schrapert\Log;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger implements LoggerInterface
{
    private $uri;

    private $stream;

    public function __construct($uri = 'php://stdout')
    {
        $this->uri = $uri;
    }

    private function getStream()
    {
        if(!$this->stream) {
            $this->stream = fopen($this->uri, 'w+');
        }
        return $this->stream;
    }

    private function interpolate($message, array $context = array()) {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        fwrite($this->getStream(), sprintf("[%s] %s - %s\n", strtoupper($level), date('Y-m-d H:i:s'), $this->interpolate($message, $context)));
    }
}