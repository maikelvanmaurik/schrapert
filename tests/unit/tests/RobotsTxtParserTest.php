<?php
class RobotsTxtParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Schrapert\Http\RobotsTxt\Parser
     */
    private $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new \Schrapert\Http\RobotsTxt\Parser();
    }

    public function testDoesTakeRobotsTxtIntoAccount()
    {
        $result = $this->parser->parse('

User-agent: UA
User-agent: Googlebot
User-agent: Moz
Allow: /
Disallow: /private/
Disallow: /secret/page.html

User-agent: *
Crawl-delay: 7
Disallow: /private/

        ');

        $this->assertTrue($result->isAllowed('Googlebot', '/public/page.html'));
        $this->assertFalse($result->isAllowed('Googlebot', '/private/page.html'));
        $this->assertTrue($result->isAllowed('Googlebot', '/something'));
        $this->assertFalse($result->isAllowed('Slupert', '/private/'));
        $this->assertFalse($result->isAllowed('Slupert', '/private/page.html'));
        $this->assertTrue($result->isAllowed('Slupert', '/public/'));
    }
}