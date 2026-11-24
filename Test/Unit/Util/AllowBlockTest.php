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

    public function testAllowsBlockNameConfiguredViaDi(): void
    {
        $allowBlock = $this->getAllowBlock(Mode::WHITELIST_CUSTOM, ['foo' => 'foo.bar']);
        $this->assertTrue($allowBlock->allow($this->getBlock(null, 'foo.bar')));
    }

    /**
     * @dataProvider getNonAllowedBlockNames
     */
    public function testDisallowsBlockNameNotConfiguredViaDi(string $blockName): void
    {
        $allowBlock = $this->getAllowBlock(Mode::WHITELIST_CUSTOM, ['foo' => 'foo.bar', 'empty' => '']);
        $this->assertFalse($allowBlock->allow($this->getBlock(null, $blockName)));
    }

    public static function getNonAllowedBlockNames(): array
    {
        return [
            [''],
            ['foo'],
            ['foo.bar.child'],
            ['FOO.BAR'],
        ];
    }

    public function testSkipsModeCheckForWhitelistedBlock(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->never())->method('getMode');

        $this->assertTrue((new AllowBlock($config))->allow($this->getBlock(true)));
    }

    private function getAllowBlock(string $mode, array $allowedBlocks = []): AllowBlock
    {
        $config = $this->createMock(Config::class);
        $config->method('getMode')->willReturn($mode);

        return new AllowBlock($config, $allowedBlocks);
    }

    private function getBlock(mixed $whitelist, string $name = ''): Template
    {
        $block = $this->createMock(Template::class);
        $block->method('getData')->with('csp_whitelist')->willReturn($whitelist);
        $block->method('getNameInLayout')->willReturn($name);

        return $block;
    }
}
