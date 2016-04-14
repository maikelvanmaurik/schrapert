<?php
namespace Schrapert\Http\RobotsTxt;

class Parser implements ParserInterface
{
    /**
     * @param $txt
     * @return ParseResultInterface
     */
    public function parse($txt)
    {
        $result = new ParseResult();
        $readingRules = false;
        $ua = [];
        foreach (preg_split('/$\R?^/m', $txt) as $line) {
            if (preg_match("/^User\-agent:\s*([^#\s]+)/", $line, $m)) {
                if($readingRules) {
                    $ua = [];
                }
                $ua[] = $m[1];
            } elseif (preg_match("/^disallow:\s*([^#\s]+)/i", $line, $m)) {
                $result->pushRule('disallow', $ua, $m[1]);
                $readingRules = true;
            } elseif (preg_match("/^crawl\-delay:\s*(\d+)/i", $line, $m)) {
                $result->pushRule('crawl-delay', $ua, $m[1]);
                $readingRules = true;
            }
        }

        return $result;
    }
}