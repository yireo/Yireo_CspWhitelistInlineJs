<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspUtilities\Util\ScriptFinder;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Util\AllowBlock;
use Yireo\CspWhitelistInlineJs\Util\TemplateScriptFilter;

class AddCspInlineScripts
{
    public function __construct(
        private readonly ReplaceInlineScripts $replaceInlineScripts,
        private readonly ScriptFinder $scriptFinder,
        private readonly TemplateScriptFilter $templateScriptFilter,
        private readonly ScriptLogger $scriptLogger,
        private readonly Config $config,
        private readonly AllowBlock $allowBlock
    ) {
    }

    public function afterToHtml(Template $block, $html): string
    {
        $html = (string)$html;
        if ($html === '' || false === stripos($html, '<script')) {
            return $html;
        }

        if (false === $this->allowBlock->allow($block)) {
            return $html;
        }

        $scripts = $this->templateScriptFilter->filter($block, $this->scriptFinder->find($html));
        if (empty($scripts)) {
            return $html;
        }

        if (true === $this->config->logging()) {
            $this->scriptLogger->add($block);
        }

        return $this->replaceInlineScripts->replaceScripts($html, $scripts);
    }
}
