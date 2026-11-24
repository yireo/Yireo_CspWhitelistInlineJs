<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Plugin;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Filesystem;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Observer\WriteLogs;
use Yireo\CspWhitelistInlineJs\Test\Integration\Stub\InlineScriptBlock;
use Yireo\CspWhitelistInlineJs\Util\AllowBlock;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoConfigFixture current_store csp_utilities/settings/inline_script_mode nonce
 */
class AddCspInlineScriptsTest extends TestCase
{
    private const HTML = "<div>\n<script>var a = 1;</script>\n"
        . "<script type=\"text/x-magento-init\">{}</script>\n"
        . "<script nonce=\"existing\">var b = 2;</script>\n"
        . "<script>var c = 3;</script>\n"
        . "<script>alert('child');</script>\n"
        . "<script src=\"https://evil.example/x.js\">;</script>\n</div>";

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode custom
     */
    public function testAddsNonceToStaticScriptsOfWhitelistedBlockInModeCustom()
    {
        $html = $this->renderBlock(['csp_whitelist' => true]);

        $this->assertSame(1, preg_match_all('#<script nonce="([^"]+)">var a = 1;#', $html, $matches));
        $this->assertNotSame('existing', $matches[1][0]);
        $this->assertContains(base64_decode($matches[1][0]), $this->getScriptSrcNonces());
        $this->assertStringContainsString('<script type="text/x-magento-init">{}</script>', $html);
        $this->assertStringContainsString('<script nonce="existing">var b = 2;</script>', $html);
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode custom
     */
    public function testSkipsScriptsNotLiterallyInTemplateOfWhitelistedBlock()
    {
        $html = $this->renderBlock(['csp_whitelist' => true]);

        $this->assertStringContainsString('<script>var c = 3;</script>', $html);
        $this->assertStringContainsString("<script>alert('child');</script>", $html);
        $this->assertStringContainsString('<script src="https://evil.example/x.js">;</script>', $html);
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode custom
     */
    public function testSkipsWhitelistedBlockWithoutTemplateFile()
    {
        $html = $this->renderBlock(['csp_whitelist' => true, 'template_file' => '']);

        $this->assertSame(self::HTML, $html);
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode custom
     */
    public function testLeavesNonWhitelistedBlockUntouchedInModeCustom()
    {
        $this->assertSame(self::HTML, $this->renderBlock([]));
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode custom
     */
    public function testAllowsBlockWhitelistedViaDiArgument()
    {
        $objectManager = ObjectManager::getInstance();
        $allowBlock = $objectManager->create(AllowBlock::class, [
            'allowedBlocks' => ['test' => 'csp.test.di.block'],
        ]);

        $layout = $objectManager->get(LayoutInterface::class);
        $this->assertTrue($allowBlock->allow($layout->createBlock(InlineScriptBlock::class, 'csp.test.di.block')));
        $this->assertFalse($allowBlock->allow($layout->createBlock(InlineScriptBlock::class, 'csp.test.other')));
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode all
     */
    public function testAddsNonceToStaticScriptsOfAnyBlockInModeAll()
    {
        $html = $this->renderBlock([]);

        $this->assertMatchesRegularExpression('#<script nonce="[^"]+">var a = 1;</script>#', $html);
        $this->assertStringContainsString('<script nonce="existing">var b = 2;</script>', $html);
        $this->assertStringContainsString("<script>alert('child');</script>", $html);
    }

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode all
     */
    public function testIgnoresModeAllInStoreScope()
    {
        $this->assertSame(self::HTML, $this->renderBlock([]));
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode custom
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/logging 1
     */
    public function testLogsTemplateOfWhitelistedBlockAtEndOfRequest()
    {
        $logDirectory = ObjectManager::getInstance()->get(Filesystem::class)->getDirectoryWrite(DirectoryList::LOG);
        $logDirectory->delete(ScriptLogger::LOG_FILE);

        try {
            $this->renderBlock(['csp_whitelist' => true]);
            $this->renderBlock([]);
            ObjectManager::getInstance()->get(WriteLogs::class)->execute(new Observer());

            $this->assertTrue($logDirectory->isExist(ScriptLogger::LOG_FILE), 'Log file was not written');
            $lines = array_filter(explode("\n", $logDirectory->readFile(ScriptLogger::LOG_FILE)));
            $this->assertCount(1, $lines);
            $this->assertStringEndsWith('Test/Integration/_files/inline-scripts.phtml', reset($lines));
        } finally {
            $logDirectory->delete(ScriptLogger::LOG_FILE);
            $logDirectory->delete(ScriptLogger::LOG_FILE . '.lock');
        }
    }

    private function renderBlock(array $data, string $name = ''): string
    {
        $data = array_merge([
            'html' => self::HTML,
            'template_file' => dirname(__DIR__) . '/_files/inline-scripts.phtml',
        ], $data);

        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        $block = $layout->createBlock(InlineScriptBlock::class, $name, ['data' => $data]);

        return $block->toHtml();
    }

    /**
     * @return string[]
     */
    private function getScriptSrcNonces(): array
    {
        $nonces = [];
        foreach (ObjectManager::getInstance()->get(DynamicCollector::class)->collect() as $policy) {
            if ($policy instanceof FetchPolicy && $policy->getId() === 'script-src') {
                $nonces = array_merge($nonces, $policy->getNonceValues());
            }
        }

        return $nonces;
    }
}
