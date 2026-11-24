<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode as ModeSource;

class Mode extends Value
{
    public function beforeSave()
    {
        $allowedValues = array_column((new ModeSource())->toOptionArray(), 'value');
        if (false === in_array((string)$this->getValue(), $allowedValues, true)) {
            throw new LocalizedException(__('Invalid CSP whitelist mode "%1"', (string)$this->getValue()));
        }

        return parent::beforeSave();
    }
}
