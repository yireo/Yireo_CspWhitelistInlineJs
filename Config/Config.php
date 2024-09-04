<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function getMode(): string
    {
        return $this->getConfigValue('csp_whitelist_inline_js/settings/mode', Mode::WHITELIST_CUSTOM);
    }

    public function logging(): bool
    {
        $logging = (bool)$this->getConfigValue('csp_whitelist_inline_js/settings/logging', false);
        if (true === $logging) {
            return true;
        }

        return false;
    }

    private function getConfigValue(string $key, $defaultValue = null)
    {
        try {
            $value = $this->scopeConfig->getValue(
                $key,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
        } catch (NoSuchEntityException $e) {
            return $defaultValue;
        }

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }
}
