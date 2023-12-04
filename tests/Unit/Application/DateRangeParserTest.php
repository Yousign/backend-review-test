<?php

namespace App\Tests\Unit\Application;

use App\Application\DateRangeParser;
use PHPUnit\Framework\TestCase;

class DateRangeParserTest extends TestCase
{
    private DateRangeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DateRangeParser();
    }


    /**
     * @dataProvider dateProvider
     * @TODO: to fix
     */
    public function testParse($date, $expected)
    {
        $this->assertEquals($expected, $this->parser->parse($date));
    }

    public function dateProvider(): ?\Generator
    {
        yield ['2012-01-01-{0..2}', ['2012-01-01-0', '2012-01-01-1', '2012-01-01-2']];
    }
}
