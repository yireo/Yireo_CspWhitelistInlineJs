<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ReplaceInlineScripts;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Config\Source\Mode;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Util\AllowBlock;

class AddCspInlineScripts
{
    private ReplaceInlineScripts $replaceInlineScripts;
    private ScriptLogger $scriptLogger;
    private Config $config;
    private AllowBlock $allowBlock;

    public function __construct(
        ReplaceInlineScripts $replaceInlineScripts,
        ScriptLogger $scriptLogger,
        Config $config,
        AllowBlock $allowBlock
    ) {
        $this->replaceInlineScripts = $replaceInlineScripts;
        $this->scriptLogger = $scriptLogger;
        $this->config = $config;
        $this->allowBlock = $allowBlock;
    }

    public function afterToHtml(Template $block, $html): string
    {
        if (false === $this->allowBlock->allow($block)) {
            return (string)$html;
        }

        if (true === $this->config->logging()) {
            $this->scriptLogger->add($block);
        }

        return $this->replaceInlineScripts->replace((string)$html);
    }
}
