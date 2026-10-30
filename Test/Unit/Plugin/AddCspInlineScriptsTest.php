<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Plugin;

use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Plugin\AddCspInlineScripts;
use Yireo\CspWhitelistInlineJs\Util\AllowBlock;

class AddCspInlineScriptsTest extends TestCase
{
    private ReplaceInlineScripts&MockObject $replaceInlineScripts;
    private ScriptLogger&MockObject $scriptLogger;
    private Config&MockObject $config;
    private AllowBlock&MockObject $allowBlock;

    protected function setUp(): void
    {
        $this->replaceInlineScripts = $this->createMock(ReplaceInlineScripts::class);
        $this->scriptLogger = $this->createMock(ScriptLogger::class);
        $this->config = $this->createMock(Config::class);
        $this->allowBlock = $this->createMock(AllowBlock::class);
    }

    public function testReturnsHtmlUntouchedForDisallowedBlock(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->with($block)->willReturn(false);
        $this->config->method('logging')->willReturn(true);
        $this->replaceInlineScripts->expects($this->never())->method('replace');
        $this->scriptLogger->expects($this->never())->method('add');

        $html = '<script>alert(1);</script>';
        $this->assertSame($html, $this->getPlugin()->afterToHtml($block, $html));
    }

    public function testReplacesInlineScriptsForAllowedBlock(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->willReturn(true);
        $this->config->method('logging')->willReturn(false);
        $this->scriptLogger->expects($this->never())->method('add');
        $this->replaceInlineScripts->expects($this->once())
            ->method('replace')
            ->with('<script>alert(1);</script>')
            ->willReturn('<script nonce="abc">alert(1);</script>');

        $this->assertSame(
            '<script nonce="abc">alert(1);</script>',
            $this->getPlugin()->afterToHtml($block, '<script>alert(1);</script>')
        );
    }

    public function testAddsBlockToLoggerWhenLoggingIsEnabled(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->willReturn(true);
        $this->config->method('logging')->willReturn(true);
        $this->scriptLogger->expects($this->once())->method('add')->with($block);
        $this->replaceInlineScripts->method('replace')->willReturnArgument(0);

        $this->getPlugin()->afterToHtml($block, '<div></div>');
    }

    public function testCastsNullHtmlToString(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->willReturn(false);

        $this->assertSame('', $this->getPlugin()->afterToHtml($block, null));
    }

    private function getPlugin(): AddCspInlineScripts
    {
        return new AddCspInlineScripts(
            $this->replaceInlineScripts,
            $this->scriptLogger,
            $this->config,
            $this->allowBlock
        );
    }
}
