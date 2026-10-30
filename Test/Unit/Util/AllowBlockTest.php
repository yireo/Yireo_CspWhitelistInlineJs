<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Util;

use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;
use Yireo\CspWhitelistInlineJs\Util\AllowBlock;

class AllowBlockTest extends TestCase
{
    public function testAllowsAnyBlockInModeAll(): void
    {
        $this->assertTrue($this->getAllowBlock(Mode::WHITELIST_ALL)->allow($this->getBlock(null)));
    }

    public function testAllowsWhitelistedBlockInModeCustom(): void
    {
        $this->assertTrue($this->getAllowBlock(Mode::WHITELIST_CUSTOM)->allow($this->getBlock(true)));
    }

    /**
     * @dataProvider getNonWhitelistedValues
     */
    public function testDisallowsBlockWithoutStrictTrueFlagInModeCustom(mixed $value): void
    {
        $this->assertFalse($this->getAllowBlock(Mode::WHITELIST_CUSTOM)->allow($this->getBlock($value)));
    }

    public static function getNonWhitelistedValues(): array
    {
        return [
            [null],
            [false],
            ['1'],
            [1],
            ['true'],
        ];
    }

    private function getAllowBlock(string $mode): AllowBlock
    {
        $config = $this->createMock(Config::class);
        $config->method('getMode')->willReturn($mode);

        return new AllowBlock($config);
    }

    private function getBlock(mixed $whitelist): Template
    {
        $block = $this->createMock(Template::class);
        $block->method('getData')->with('csp_whitelist')->willReturn($whitelist);

        return $block;
    }
}
