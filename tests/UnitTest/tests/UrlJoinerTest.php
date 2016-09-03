<?php
class UrlJoinerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Schrapert\Http\UrlJoiner
     */
    private $joiner;

    public function setUp()
    {
        parent::setUp();
        $this->joiner = new \Schrapert\Http\UrlJoiner();
    }

    public function testDoesJoinRelativePathsCorrectly()
    {
        $joined = $this->joiner->join('/test.php', 'http://www.schrapert.dev');

        $this->assertEquals('http://www.schrapert.dev/test.php', $joined);

        $joined = $this->joiner->join('../test.php', 'http://www.schrapert.dev/foo/bar/');

        $this->assertEquals('http://www.schrapert.dev/foo/test.php', $joined);

        $joined = $this->joiner->join('test.php', 'http://www.schrapert.dev/foo/bar/');

        $this->assertEquals('http://www.schrapert.dev/foo/bar/test.php', $joined);
    }
}