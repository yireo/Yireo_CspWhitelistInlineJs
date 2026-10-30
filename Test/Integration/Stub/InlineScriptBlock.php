<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Stub;

use Magento\Framework\View\Element\Template;

class InlineScriptBlock extends Template
{
    protected function _toHtml()
    {
        return (string)$this->getData('html');
    }
}
