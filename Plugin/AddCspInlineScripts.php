<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Util\ScriptFinder;
use Yireo\CspWhitelistInlineJs\Util\SecureHtmlRendererStub;

class AddCspInlineScripts
{
    private ScriptFinder $scriptFinder;
    private SecureHtmlRendererStub $secureHtmlRendererStub;
    private ScriptLogger $scriptLogger;

    public function __construct(
        ScriptFinder $scriptFinder,
        SecureHtmlRendererStub $secureHtmlRendererStub,
        ScriptLogger $scriptLogger
    ) {
        $this->scriptFinder = $scriptFinder;
        $this->secureHtmlRendererStub = $secureHtmlRendererStub;
        $this->scriptLogger = $scriptLogger;
    }

    public function afterFetchView(Template $block, string $html): string
    {
        $scripts = $this->scriptFinder->find($html);
        if (empty($scripts)) {
            return $html;
        }

        $this->scriptLogger->log($block);
        foreach ($scripts as $fullScript => $inlineJs) {
            $newScript = $this->secureHtmlRendererStub->renderTag('script', [], $inlineJs, false);
            $html = str_replace($fullScript, $newScript, $html);
        }

        return $html;
    }
}
