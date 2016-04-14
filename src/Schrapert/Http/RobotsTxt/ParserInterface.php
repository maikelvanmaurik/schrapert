<?php
namespace Schrapert\Http\RobotsTxt;

interface ParserInterface
{
    /**
     * @param $txt
     * @return ParseResultInterface
     */
    public function parse($txt);
}