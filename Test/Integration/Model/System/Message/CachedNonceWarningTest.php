<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Model\System\Message;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Notification\MessageList;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Model\System\Message\CachedNonceWarning;

/**
 * @magentoAppArea adminhtml
 */
class CachedNonceWarningTest extends TestCase
{
    private bool $fullPageCacheEnabled = false;

    protected function setUp(): void
    {
        $this->fullPageCacheEnabled = $this->getCacheState()->isEnabled('full_page');
    }

    protected function tearDown(): void
    {
        $this->getCacheState()->setEnabled('full_page', $this->fullPageCacheEnabled);
    }

    public function testIsRegisteredInMessageList()
    {
        $message = ObjectManager::getInstance()->get(MessageList::class)
            ->getMessageByIdentity('yireo_csp_whitelist_inline_js_cached_nonce');
        $this->assertInstanceOf(CachedNonceWarning::class, $message);
    }

    /**
     * @magentoConfigFixture current_store csp_utilities/settings/inline_script_mode nonce
     * @magentoConfigFixture system/full_page_cache/caching_application 1
     */
    public function testIsDisplayedWithNoncesAndBuiltInFullPageCache()
    {
        $this->getCacheState()->setEnabled('full_page', true);
        $message = $this->getMessage();
        $this->assertTrue($message->isDisplayed());
        $this->assertStringContainsString('Yireo_PageCacheCspNonceRegenerator', $message->getText());
    }

    /**
     * @magentoConfigFixture current_store csp_utilities/settings/inline_script_mode nonce
     * @magentoConfigFixture system/full_page_cache/caching_application 2
     */
    public function testIsDisplayedWithNoncesAndVarnish()
    {
        $this->getCacheState()->setEnabled('full_page', true);
        $message = $this->getMessage();
        $this->assertTrue($message->isDisplayed());
        $this->assertStringContainsString('Varnish', $message->getText());
    }

    /**
     * @magentoConfigFixture current_store csp_utilities/settings/inline_script_mode hash
     */
    public function testIsHiddenWithHashes()
    {
        $this->getCacheState()->setEnabled('full_page', true);
        $this->assertFalse($this->getMessage()->isDisplayed());
    }

    /**
     * @magentoConfigFixture current_store csp_utilities/settings/inline_script_mode nonce
     */
    public function testIsHiddenWithoutFullPageCache()
    {
        $this->getCacheState()->setEnabled('full_page', false);
        $this->assertFalse($this->getMessage()->isDisplayed());
    }

    private function getMessage(): CachedNonceWarning
    {
        return ObjectManager::getInstance()->create(CachedNonceWarning::class);
    }

    private function getCacheState(): CacheState
    {
        return ObjectManager::getInstance()->get(CacheState::class);
    }
}
