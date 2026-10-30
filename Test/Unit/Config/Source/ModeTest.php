<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Config\Source;

use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;

class ModeTest extends TestCase
{
    public function testToOptionArray(): void
    {
        $options = (new Mode())->toOptionArray();

        $this->assertSame(['custom', 'all'], array_column($options, 'value'));
    }
}
