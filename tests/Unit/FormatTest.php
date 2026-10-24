<?php

namespace Tests\Unit;

use App\Support\Format;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public function test_normalisasi_nomor_whatsapp(): void
    {
        $this->assertSame('08676767676', Format::normalizeWhatsapp('0867-6767-676'));
        $this->assertSame('082336478234', Format::normalizeWhatsapp('+62 823 3647 8234'));
        $this->assertSame('082336478234', Format::normalizeWhatsapp('82336478234'));
    }

    public function test_nomor_wa_internasional(): void
    {
        $this->assertSame('6282336478234', Format::waNumber('082336478234'));
    }

    public function test_inisial(): void
    {
        $this->assertSame('YU', Format::initials('Yudistira'));
        $this->assertSame('PJ', Format::initials('Pak Jeki'));
        $this->assertSame('?', Format::initials('  '));
    }

    public function test_format_meter_gaya_indonesia(): void
    {
        $this->assertSame('1,25', Format::meters(1.25));
        $this->assertSame('-', Format::meters(null));
    }
}
