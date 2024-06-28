<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Plugin;

use Magento\Framework\View\Element\Template;
use Yireo\CspWhitelistInlineJs\Util\ScriptFinder;
use Yireo\CspWhitelistInlineJs\Util\SecureHtmlRendererStub;

class AddCspInlineScripts
{
    private ScriptFinder $scriptFinder;
    private SecureHtmlRendererStub $secureHtmlRendererStub;

    public function __construct(
        ScriptFinder $scriptFinder,
        SecureHtmlRendererStub $secureHtmlRendererStub
    ) {
        $this->scriptFinder = $scriptFinder;
        $this->secureHtmlRendererStub = $secureHtmlRendererStub;
    }

    public function afterFetchView(Template $block, string $html): string
    {
        $scripts = $this->scriptFinder->find($html);
        foreach ($scripts as $fullScript => $inlineJs) {
            $newScript = $this->secureHtmlRendererStub->renderTag('script', [], $inlineJs, false);
            $html = str_replace($fullScript, $newScript, $html);
        }

        return $html;
    }
}
