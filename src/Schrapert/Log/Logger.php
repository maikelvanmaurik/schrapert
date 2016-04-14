<?php
namespace Schrapert\Log;

class Logger implements LoggerInterface
{
    const INFO = 1;

    const WARNING = 2;

    const ERROR = 4;

    const CRITICAL = 8;

    const DEBUG = 16;

    const ALL = 31;

    public function __construct($path, $level)
    {
        $this->path = $path;
        $this->level = $level;
    }

    public function isEnabledFor($level)
    {

        return $level === ($this->level & $level);
    }

    private function formatMessage($level, $message, array $args = [])
    {
        if(!empty($args)) {
            $message = vsprintf($message, $args);
        }
        $strLevel = '';
        switch($level) {
            case self::INFO:
                $strLevel = 'INFO';
                break;
            case self::WARNING:
                $strLevel = 'WARNING';
                break;
            case self::ERROR:
                $strLevel = 'ERROR';
                break;
            case self::DEBUG:
                $strLevel = 'DEBUG';
                break;
        }
        return sprintf('%s [%s] %s', date('Y-m-d H:i:s'), $strLevel, $message);
    }

    private function _log($level, $message, array $args = [])
    {
        $fp = fopen($this->path, 'a+');
        if(flock($fp, LOCK_EX)) {
            fwrite($fp, $this->formatMessage($level, $message, $args) . "\n");
            flock($fp, LOCK_UN);
        }
    }

    public function log($level, $message, array $args = [])
    {
        if($this->isEnabledFor($level)) {
            $this->_log($level, $message, $args);
        }
    }

    public function info($message, array $args = [])
    {
        if($this->isEnabledFor(self::INFO)) {
            $this->log(self::INFO, $message, $args);
        }
    }

    public function warning($message, array $args = [])
    {
        if ($this->isEnabledFor(self::INFO)) {
            $this->log(self::WARNING, $message, $args);
        }
    }

    public function error($message, array $args = [])
    {
        if($this->isEnabledFor(self::ERROR)) {
            $this->log(self::INFO, $message, $args);
        }
    }

    public function debug($message, array $args = [])
    {
        if($this->isEnabledFor(self::DEBUG)) {
            $this->log(self::DEBUG, $message, $args);
        }
    }

}