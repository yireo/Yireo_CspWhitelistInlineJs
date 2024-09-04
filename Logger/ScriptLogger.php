<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Logger;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;

class ScriptLogger
{
    private DirectoryList $directoryList;
    private Filesystem $filesystem;

    private array $templates = [];

    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
    }

    public function add(Template $block): bool
    {
        $template = $block->getTemplateFile();
        if (empty($template)) {
            return false;
        }

        $reader = $this->filesystem->getDirectoryRead($this->directoryList::ROOT);
        if (false === $reader->isFile($template)) {
            return false;
        }

        $this->templates[] = $template;
        return true;
    }

    public function log()
    {
        $logFile = 'yireo-csp-whitelist-inline-js.log';

        $writer = $this->filesystem->getDirectoryWrite($this->directoryList::LOG);
        if ($writer->isExist($logFile)) {
            $currentTemplates = explode("\n", trim($writer->readFile($logFile)));
        } else {
            $currentTemplates = [];
        }

        $templates = array_merge($currentTemplates, $this->templates);
        $templates = array_unique($templates);
        sort($templates);

        $this->templates = [];

        $contents = implode("\n", $templates)."\n";
        $writer->writeFile($logFile, $contents, 'w+');
    }
}
