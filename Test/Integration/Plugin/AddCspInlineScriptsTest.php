<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Plugin;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Test\Integration\Stub\InlineScriptBlock;

/**
 * @magentoAppArea frontend
 * @magentoConfigFixture current_store csp_utilities/settings/inline_script_mode nonce
 */
class AddCspInlineScriptsTest extends TestCase
{
    private const HTML = '<div><script>var a = 1;</script>'
        . '<script type="text/x-magento-init">{}</script>'
        . '<script nonce="existing">var b = 2;</script></div>';

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode custom
     */
    public function testAddsNonceToWhitelistedBlockInModeCustom()
    {
        $html = $this->renderBlock(true);

        $this->assertSame(1, preg_match_all('#<script nonce="([^"]+)">var a = 1;#', $html, $matches));
        $this->assertNotSame('existing', $matches[1][0]);
        $this->assertContains(base64_decode($matches[1][0]), $this->getScriptSrcNonces());
        $this->assertStringContainsString('<script type="text/x-magento-init">{}</script>', $html);
        $this->assertStringContainsString('<script nonce="existing">var b = 2;</script>', $html);
    }

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode custom
     */
    public function testLeavesNonWhitelistedBlockUntouchedInModeCustom()
    {
        $this->assertSame(self::HTML, $this->renderBlock(null));
    }

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode all
     */
    public function testAddsNonceToAnyBlockInModeAll()
    {
        $html = $this->renderBlock(null);

        $this->assertMatchesRegularExpression('#<script nonce="[^"]+">var a = 1;</script>#', $html);
        $this->assertStringContainsString('<script nonce="existing">var b = 2;</script>', $html);
    }

    private function renderBlock(?bool $whitelist): string
    {
        $data = ['html' => self::HTML];
        if ($whitelist !== null) {
            $data['csp_whitelist'] = $whitelist;
        }

        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        $block = $layout->createBlock(InlineScriptBlock::class, '', ['data' => $data]);

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
