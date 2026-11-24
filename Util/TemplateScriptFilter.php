<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Util;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\View\Element\Template;
use Yireo\CspUtilities\Util\ScriptFinder;

class TemplateScriptFilter
{
    private array $templateScripts = [];

    public function __construct(
        private readonly ScriptFinder $scriptFinder,
        private readonly FileDriver $fileDriver
    ) {
    }

    /**
     * Only keep the rendered scripts that are literally present in the template file of the block itself
     *
     * @param Template $block
     * @param string[] $scripts
     * @return string[]
     */
    public function filter(Template $block, array $scripts): array
    {
        if (empty($scripts)) {
            return [];
        }

        $templateScripts = $this->getTemplateScripts((string)$block->getTemplateFile());
        if (empty($templateScripts)) {
            return [];
        }

        return array_values(array_filter(
            $scripts,
            fn (string $script): bool => in_array($script, $templateScripts, true)
        ));
    }

    /**
     * @param string $templateFile
     * @return string[]
     */
    private function getTemplateScripts(string $templateFile): array
    {
        if ($templateFile === '') {
            return [];
        }

        if (false === array_key_exists($templateFile, $this->templateScripts)) {
            $this->templateScripts[$templateFile] = $this->findStaticScripts($templateFile);
        }

        return $this->templateScripts[$templateFile];
    }

    /**
     * @param string $templateFile
     * @return string[]
     */
    private function findStaticScripts(string $templateFile): array
    {
        try {
            if (false === $this->fileDriver->isFile($templateFile)) {
                return [];
            }

            $source = $this->fileDriver->fileGetContents($templateFile);
        } catch (FileSystemException $fileSystemException) {
            return [];
        }

        return array_values(array_filter(
            $this->scriptFinder->find($source),
            fn (string $script): bool => false === str_contains($script, '<?')
        ));
    }
}
