<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;

class AddCspInlineScripts
{
    private ReplaceInlineScripts $replaceInlineScripts;
    private ScriptLogger $scriptLogger;

    public function __construct(
        ReplaceInlineScripts $replaceInlineScripts,
        ScriptLogger $scriptLogger
    ) {
        $this->replaceInlineScripts = $replaceInlineScripts;
        $this->scriptLogger = $scriptLogger;

    }

    public function afterToHtml(Template $block, $html): string
    {
        $this->scriptLogger->log($block);
        return $this->replaceInlineScripts->replace((string)$html);
    }
}
