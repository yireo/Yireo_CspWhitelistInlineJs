<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Model\System\Message;

use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Notification\MessageInterface;
use Yireo\CspUtilities\Config\Config as CspUtilitiesConfig;

class CachedNonceWarning implements MessageInterface
{
    private const CACHING_APPLICATION_PATH = 'system/full_page_cache/caching_application';
    private const CACHING_APPLICATION_VARNISH = 2;

    public function __construct(
        private readonly CspUtilitiesConfig $cspUtilitiesConfig,
        private readonly CacheState $cacheState,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ModuleManager $moduleManager
    ) {
    }

    public function getIdentity()
    {
        return 'yireo_csp_whitelist_inline_js_cached_nonce';
    }

    public function isDisplayed()
    {
        if (false === $this->cspUtilitiesConfig->generateNonces()) {
            return false;
        }

        if (false === $this->cacheState->isEnabled('full_page')) {
            return false;
        }

        if ($this->isVarnish()) {
            return true;
        }

        return false === $this->moduleManager->isEnabled('Yireo_PageCacheCspNonceRegenerator');
    }

    public function getText()
    {
        if ($this->isVarnish()) {
            return (string)__(
                'Yireo CspWhitelistInlineJs adds CSP nonces to inline scripts, but with Varnish these nonces are '
                . 'cached and shared between visitors. Switch the CSP Utilities "Inline Script Mode" to "hash".'
            );
        }

        return (string)__(
            'Yireo CspWhitelistInlineJs adds CSP nonces to inline scripts, but with the Full Page Cache enabled, '
            . 'these nonces are cached and shared between visitors. Install the module '
            . 'Yireo_PageCacheCspNonceRegenerator or switch the CSP Utilities "Inline Script Mode" to "hash".'
        );
    }

    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    private function isVarnish(): bool
    {
        return (int)$this->scopeConfig->getValue(self::CACHING_APPLICATION_PATH) === self::CACHING_APPLICATION_VARNISH;
    }
}
