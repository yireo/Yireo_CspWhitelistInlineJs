<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Config;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Config;

/**
 * @magentoAppArea frontend
 */
class ConfigTest extends TestCase
{
    public function testDefaults()
    {
        $config = ObjectManager::getInstance()->create(Config::class);
        $this->assertSame('custom', $config->getMode());
        $this->assertFalse($config->logging());
    }

    /**
     * @magentoConfigFixture csp_whitelist_inline_js/settings/mode all
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/logging 1
     */
    public function testDefaultModeAndStoreViewLoggingAreUsed()
    {
        $config = ObjectManager::getInstance()->create(Config::class);
        $this->assertSame('all', $config->getMode());
        $this->assertTrue($config->logging());
    }

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode all
     */
    public function testStoreViewModeIsIgnored()
    {
        $config = ObjectManager::getInstance()->create(Config::class);
        $this->assertSame('custom', $config->getMode());
    }
}
