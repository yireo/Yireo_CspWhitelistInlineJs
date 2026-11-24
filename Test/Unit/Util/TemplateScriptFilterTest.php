<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Util;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use Yireo\CspUtilities\Util\ScriptFinder;
use Yireo\CspWhitelistInlineJs\Util\TemplateScriptFilter;

class TemplateScriptFilterTest extends TestCase
{
    private const TEMPLATE = <<<'PHTML'
<?php declare(strict_types=1); ?>
<div><?= $block->getChildHtml() ?></div>
<script>var a = 1;</script>
<script>var b = <?= (int)$block->getData('b') ?>;</script>
<script type="text/javascript" defer>
    var c = 3;
</script>
PHTML;

    private string $templateFile = '';

    protected function setUp(): void
    {
        $this->templateFile = (string)tempnam(sys_get_temp_dir(), 'csp');
        file_put_contents($this->templateFile, self::TEMPLATE);
    }

    protected function tearDown(): void
    {
        if (is_file($this->templateFile)) {
            unlink($this->templateFile);
        }
    }

    public function testKeepsOnlyStaticScriptsOfOwnTemplate(): void
    {
        $static = '<script>var a = 1;</script>';
        $staticMultiline = "<script type=\"text/javascript\" defer>\n    var c = 3;\n</script>";
        $scripts = [
            $static,
            '<script>var b = 2;</script>',
            '<script>alert("child");</script>',
            $staticMultiline,
        ];

        $filter = new TemplateScriptFilter(new ScriptFinder(), new FileDriver());
        $this->assertSame([$static, $staticMultiline], $filter->filter($this->getBlock($this->templateFile), $scripts));
    }

    public function testDoesNotMatchScriptWithAddedAttributes(): void
    {
        $filter = new TemplateScriptFilter(new ScriptFinder(), new FileDriver());
        $block = $this->getBlock($this->templateFile);

        $this->assertSame([], $filter->filter($block, ['<script src="/x.js">var a = 1;</script>']));
        $this->assertSame([], $filter->filter($block, ['<script defer>var a = 1;</script>']));
    }

    public function testNeverMatchesScriptsWithPhpCodeInTemplate(): void
    {
        $filter = new TemplateScriptFilter(new ScriptFinder(), new FileDriver());
        $script = '<script>var b = <?= (int)$block->getData(\'b\') ?>;</script>';

        $this->assertSame([], $filter->filter($this->getBlock($this->templateFile), [$script]));
    }

    /**
     * @dataProvider getInvalidTemplateFiles
     */
    public function testReturnsNothingWithoutReadableTemplate(mixed $templateFile): void
    {
        $filter = new TemplateScriptFilter(new ScriptFinder(), new FileDriver());

        $this->assertSame([], $filter->filter($this->getBlock($templateFile), ['<script>var a = 1;</script>']));
    }

    public static function getInvalidTemplateFiles(): array
    {
        return [
            [false],
            [''],
            ['/does/not/exist.phtml'],
            [sys_get_temp_dir()],
        ];
    }

    public function testReturnsNothingWhenReadingTemplateFails(): void
    {
        $fileDriver = $this->createMock(FileDriver::class);
        $fileDriver->method('isFile')->willThrowException(new FileSystemException(new Phrase('error')));

        $filter = new TemplateScriptFilter(new ScriptFinder(), $fileDriver);
        $this->assertSame([], $filter->filter($this->getBlock('/foo.phtml'), ['<script>var a = 1;</script>']));
    }

    public function testReadsTemplateOnlyOnce(): void
    {
        $fileDriver = $this->createMock(FileDriver::class);
        $fileDriver->method('isFile')->willReturn(true);
        $fileDriver->expects($this->once())->method('fileGetContents')->willReturn(self::TEMPLATE);

        $filter = new TemplateScriptFilter(new ScriptFinder(), $fileDriver);
        $block = $this->getBlock('/foo.phtml');
        $script = '<script>var a = 1;</script>';
        $this->assertSame(
            [[$script], [$script]],
            [$filter->filter($block, [$script]), $filter->filter($block, [$script])]
        );
    }

    public function testSkipsTemplateLookupWithoutScripts(): void
    {
        $block = $this->createMock(Template::class);
        $block->expects($this->never())->method('getTemplateFile');

        $this->assertSame([], (new TemplateScriptFilter(new ScriptFinder(), new FileDriver()))->filter($block, []));
    }

    private function getBlock(mixed $templateFile): Template
    {
        $block = $this->createMock(Template::class);
        $block->method('getTemplateFile')->willReturn($templateFile);

        return $block;
    }
}
