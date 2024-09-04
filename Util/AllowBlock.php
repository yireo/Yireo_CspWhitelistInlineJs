<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Util;

use Magento\Framework\View\Element\Template;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;

class AllowBlock
{
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function allow(Template $block): bool
    {

        if ($this->config->getMode() === Mode::WHITELIST_ALL) {
            return true;
        }

        if ($block->getData('csp_whitelist') === true) {
            return true;
        }

        return false;
    }
}
