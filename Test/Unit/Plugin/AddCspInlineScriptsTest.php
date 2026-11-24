<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Plugin;

use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspUtilities\Util\ScriptFinder;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Plugin\AddCspInlineScripts;
use Yireo\CspWhitelistInlineJs\Util\AllowBlock;
use Yireo\CspWhitelistInlineJs\Util\TemplateScriptFilter;

class AddCspInlineScriptsTest extends TestCase
{
    private const SCRIPT = '<script>alert(1);</script>';

    private ReplaceInlineScripts&MockObject $replaceInlineScripts;
    private TemplateScriptFilter&MockObject $templateScriptFilter;
    private ScriptLogger&MockObject $scriptLogger;
    private Config&MockObject $config;
    private AllowBlock&MockObject $allowBlock;

    protected function setUp(): void
    {
        $this->replaceInlineScripts = $this->createMock(ReplaceInlineScripts::class);
        $this->templateScriptFilter = $this->createMock(TemplateScriptFilter::class);
        $this->scriptLogger = $this->createMock(ScriptLogger::class);
        $this->config = $this->createMock(Config::class);
        $this->allowBlock = $this->createMock(AllowBlock::class);
    }

    public function testReturnsHtmlUntouchedForDisallowedBlock(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->with($block)->willReturn(false);
        $this->config->method('logging')->willReturn(true);
        $this->templateScriptFilter->expects($this->never())->method('filter');
        $this->replaceInlineScripts->expects($this->never())->method('replaceScripts');
        $this->scriptLogger->expects($this->never())->method('add');

        $this->assertSame(self::SCRIPT, $this->getPlugin()->afterToHtml($block, self::SCRIPT));
    }

    public function testSkipsAllChecksForHtmlWithoutScripts(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->expects($this->never())->method('allow');
        $this->replaceInlineScripts->expects($this->never())->method('replaceScripts');

        $this->assertSame('<div></div>', $this->getPlugin()->afterToHtml($block, '<div></div>'));
    }

    public function testReplacesOnlyFilteredScriptsForAllowedBlock(): void
    {
        $block = $this->createMock(Template::class);
        $html = self::SCRIPT . '<script>alert(2);</script>';
        $this->allowBlock->method('allow')->willReturn(true);
        $this->config->method('logging')->willReturn(false);
        $this->scriptLogger->expects($this->never())->method('add');
        $this->templateScriptFilter->expects($this->once())
            ->method('filter')
            ->with($block, [self::SCRIPT, '<script>alert(2);</script>'])
            ->willReturn([self::SCRIPT]);
        $this->replaceInlineScripts->expects($this->once())
            ->method('replaceScripts')
            ->with($html, [self::SCRIPT])
            ->willReturn('<script nonce="abc">alert(1);</script><script>alert(2);</script>');

        $this->assertSame(
            '<script nonce="abc">alert(1);</script><script>alert(2);</script>',
            $this->getPlugin()->afterToHtml($block, $html)
        );
    }

    public function testReturnsHtmlUntouchedWhenNoScriptIsInTemplate(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->willReturn(true);
        $this->config->method('logging')->willReturn(true);
        $this->templateScriptFilter->method('filter')->willReturn([]);
        $this->scriptLogger->expects($this->never())->method('add');
        $this->replaceInlineScripts->expects($this->never())->method('replaceScripts');

        $this->assertSame(self::SCRIPT, $this->getPlugin()->afterToHtml($block, self::SCRIPT));
    }

    public function testAddsBlockToLoggerWhenLoggingIsEnabled(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->willReturn(true);
        $this->config->method('logging')->willReturn(true);
        $this->templateScriptFilter->method('filter')->willReturn([self::SCRIPT]);
        $this->scriptLogger->expects($this->once())->method('add')->with($block);
        $this->replaceInlineScripts->method('replaceScripts')->willReturnArgument(0);

        $this->getPlugin()->afterToHtml($block, self::SCRIPT);
    }

    public function testCastsNullHtmlToString(): void
    {
        $block = $this->createMock(Template::class);
        $this->allowBlock->method('allow')->willReturn(true);

        $this->assertSame('', $this->getPlugin()->afterToHtml($block, null));
    }

    private function getPlugin(): AddCspInlineScripts
    {
        return new AddCspInlineScripts(
            $this->replaceInlineScripts,
            new ScriptFinder(),
            $this->templateScriptFilter,
            $this->scriptLogger,
            $this->config,
            $this->allowBlock
        );
    }
}
