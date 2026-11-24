<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Util;

use Magento\Framework\View\Element\Template;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;

class AllowBlock
{
    /**
     * @param Config $config
     * @param string[] $allowedBlocks
     */
    public function __construct(
        private readonly Config $config,
        private readonly array $allowedBlocks = []
    ) {
    }

    public function allow(Template $block): bool
    {
        if ($block->getData('csp_whitelist') === true) {
            return true;
        }

        $blockName = (string)$block->getNameInLayout();
        if ($blockName !== '' && in_array($blockName, $this->allowedBlocks, true)) {
            return true;
        }

        return $this->config->getMode() === Mode::WHITELIST_ALL;
    }
}
