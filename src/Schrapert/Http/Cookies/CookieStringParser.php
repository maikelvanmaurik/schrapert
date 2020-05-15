<?php

namespace Schrapert\Http\Cookies;

use Schrapert\Http\Cookies\Exceptions\ParseException;

use function Schrapert\str_camel;
use function Schrapert\str_studly;

class CookieStringParser implements CookieStringParserInterface
{
    public function parse(string $string, CookieInterface $cookie) : void
    {
        // Create the default return array
        $defaults = [
            'name' => null,
            'value' => null,
            'domain' => null,
            'path' => '/',
            'maxAge' => null,
            'expires' => null,
            'secure' => false,
            'discard' => false,
            'httpOnly' => false
        ];
        $data = $defaults;
        // Explode the cookie string using a series of semicolons
        $pieces = array_filter(array_map('trim', explode(';', $string)));
        // The name of the cookie (first kvp) must include an equal sign.
        if (empty($pieces) || ! strpos($pieces[0], '=')) {
            throw new ParseException("Invalid cookie '$string'");
        }

        // Add the cookie pieces into the parsed data array
        foreach ($pieces as $part) {
            $cookieParts = explode('=', $part, 2);
            $key = trim($cookieParts[0]);
            $value = isset($cookieParts[1])
                ? trim($cookieParts[1], " \n\r\t\0\x0B")
                : true;

            // Only check for non-cookies when cookies have been found
            if (empty($data['name'])) {
                $data['name'] = $key;
                $data['value'] = $value;
            } else {
                foreach (array_keys($defaults) as $search) {
                    if (! strcasecmp($search, $key)) {
                        $data[$search] = $value;
                        continue 2;
                    }
                }
                $data[$key] = $value;
            }
        }

        foreach ($data as $key => $value) {
            $setter = 'set'.str_studly($key);
            if (method_exists($cookie, $setter)) {
                $cookie->$setter($value);
            }
        }
    }
}
