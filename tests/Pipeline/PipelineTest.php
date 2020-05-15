<?php

namespace Schrapert\Tests\Pipeline;

use Schrapert\Pipeline\Pipeline;
use Schrapert\Tests\TestCase;

use function React\Promise\resolve;

class PipelineTest extends TestCase
{
    public function testPipelineCanBeUsedWithPromises()
    {
        $pipeline = new Pipeline(
            function (callable $next) {
                return function ($text) use ($next) {
                    return resolve($next($text.' is a'));
                };
            },
            function (callable $next) {
                return function ($text) use ($next) {
                    return resolve($next($text.' test'));
                };
            }
        );
        $value = null;
        resolve($pipeline('this'))->then(function ($result) use (&$value) {
            $value = $result;
        });
        $this->assertSame('this is a test', $value);
    }

    public function testValuesArePassedToNextPipe()
    {
        $pipeline = new Pipeline(
            function (callable $next) {
                return function ($text) use ($next) {
                    return $next($text.' is');
                };
            },
            function (callable $next) {
                return function ($text) use ($next) {
                    return $next($text.' a test');
                };
            }
        );
        $value = null;
        $pipeline('this')->then(function ($result) use (&$value) {
            $value = $result;
        });
        $this->assertSame('this is a test', $value);
    }

    public function testDownstreamPipe()
    {
        $pipeline = new Pipeline(
            function (callable $next) {
                return function ($text) use ($next) {
                    $promise = $next($text);
                    return $promise->then(function ($text) {
                        return resolve($text.' test');
                    });
                };
            },
            function (callable $next) {
                return function ($text) use ($next) {
                    return resolve($next($text.' is a'));
                };
            }
        );
        $value = null;
        resolve($pipeline('this'))->then(function ($result) use (&$value) {
            $value = $result;
        });
        $this->assertSame('this is a test', $value);
    }

    public function testItsPossibleToTapIntoThePipelineBeforeAndAfterwards()
    {
        $pipeline = new Pipeline(
            function (callable $next) {
                return function ($words) use ($next) {
                    $words[] = 'is';
                    $promise = $next($words);
                    return $promise->then(function ($words) {
                        $words[] = 'test';
                        return resolve($words);
                    });
                };
            },
            function (callable $next) {
                return function ($words) use ($next) {
                    $words[] = 'a';
                    return resolve($next($words));
                };
            }
        );
        $value = null;
        resolve($pipeline(['this']))->then(function ($result) use (&$value) {
            $value = implode(' ', $result);
        });
        $this->assertSame('this is a test', $value);
    }
}
