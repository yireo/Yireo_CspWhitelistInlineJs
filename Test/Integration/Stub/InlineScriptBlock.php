<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Stub;

use Magento\Framework\View\Element\Template;

class InlineScriptBlock extends Template
{
    public function getTemplateFile($template = null)
    {
        return (string)$this->getData('template_file');
    }

    protected function _toHtml()
    {
        return (string)$this->getData('html');
    }
}
