<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    const WHITELIST_CUSTOM = 'custom';
    const WHITELIST_ALL = 'all';

    public function toOptionArray()
    {
        return [
            ['value' => self::WHITELIST_CUSTOM, 'label' => __('Custom whitelisting')],
            ['value' => self::WHITELIST_ALL, 'label' => __('Whitelist everything (DANGEROUS)')],
        ];
    }
}
