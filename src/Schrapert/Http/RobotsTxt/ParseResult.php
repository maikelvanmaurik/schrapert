<?php

namespace Schrapert\Http\RobotsTxt;

class ParseResult implements ParseResultInterface
{
    private $rules;

    public function __construct()
    {
        $this->rules = [];
    }

    public function pushRule($type, $ua, $value)
    {
        $this->rules[] = [$type, (array)$ua, $value];
    }

    /**
     * @param string $ua
     * @param string $path
     * @return boolean true if allowed; otherwise, false.
     */
    public function isAllowed($ua, $path = '/')
    {
        foreach ($this->rules as $rule) {
            list($type, $agents, $value) = $rule;

            if (! in_array('*', $agents) && ! in_array($ua, $agents)) {
                continue;
            }

            if ($type !== 'disallow') {
                continue;
            }

            $disallow = preg_quote($value, '/');
            $last = substr($disallow, -1);
            if ($last !== '*' && $last !== '$') {
                $disallow .= '*';
            }

            $disallow = str_replace(['\*', '\$'], ['*', '$'], $disallow);
            $disallow = str_replace('*', '(.*)?', $disallow);

            if (preg_match('/^'.$disallow.'/i', $path)) {
                return false;
            }
        }
        return true;
    }
}
