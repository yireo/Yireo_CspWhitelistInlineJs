<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;

class Config
{
    public const ALLOW_ALL_IN_PRODUCTION = 'csp_whitelist_inline_js/allow_all_in_production';

    private ?string $mode = null;
    private array $logging = [];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly State $appState,
        private readonly DeploymentConfig $deploymentConfig
    ) {
    }

    public function getMode(): string
    {
        if ($this->mode === null) {
            $this->mode = $this->resolveMode();
        }

        return $this->mode;
    }

    public function logging(): bool
    {
        try {
            $store = $this->storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            return false;
        }

        $storeId = (int)$store->getId();
        if (false === array_key_exists($storeId, $this->logging)) {
            $this->logging[$storeId] = (bool)$this->scopeConfig->getValue(
                'csp_whitelist_inline_js/settings/logging',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }

        return $this->logging[$storeId];
    }

    private function resolveMode(): string
    {
        $mode = (string)$this->scopeConfig->getValue('csp_whitelist_inline_js/settings/mode');
        if ($mode !== Mode::WHITELIST_ALL) {
            return Mode::WHITELIST_CUSTOM;
        }

        if ($this->appState->getMode() !== State::MODE_PRODUCTION) {
            return Mode::WHITELIST_ALL;
        }

        if (true === (bool)$this->deploymentConfig->get(self::ALLOW_ALL_IN_PRODUCTION)) {
            return Mode::WHITELIST_ALL;
        }

        return Mode::WHITELIST_CUSTOM;
    }
}
