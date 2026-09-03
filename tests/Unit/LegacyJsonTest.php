<?php

namespace Tests\Unit;

use App\Support\LegacyJson;
use PHPUnit\Framework\TestCase;

class LegacyJsonTest extends TestCase
{
    public function test_money_formats_vietnamese_thousands(): void
    {
        $this->assertSame('20.000', LegacyJson::money('20000'));
        $this->assertSame('99.000', LegacyJson::money('99000'));
        $this->assertSame('1.599.000', LegacyJson::money('1599000'));
        $this->assertSame('0', LegacyJson::money('0'));
        $this->assertSame('', LegacyJson::money(''));
    }

    public function test_parse_money_strips_formatting(): void
    {
        $this->assertSame('20000', LegacyJson::parseMoney('20.000'));
        $this->assertSame('99000', LegacyJson::parseMoney('99.000'));
        $this->assertSame('499000', LegacyJson::parseMoney('499.000'));
        $this->assertSame('1000', LegacyJson::parseMoney('1000'));
    }
}
