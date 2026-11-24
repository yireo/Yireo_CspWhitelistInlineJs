<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\View\Element\Template;

class ScriptLogger
{
    public const LOG_FILE = 'yireo-csp-whitelist-inline-js.log';

    private array $templates = [];

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly Filesystem $filesystem,
        private readonly FileDriver $fileDriver
    ) {
    }

    public function add(Template $block): bool
    {
        try {
            $template = (string)$block->getTemplateFile();
            if ($template === '' || false === $this->fileDriver->isFile($template)) {
                return false;
            }
        } catch (FileSystemException $fileSystemException) {
            return false;
        }

        $this->templates[] = $this->getRelativePath($template);
        return true;
    }

    public function log(): void
    {
        if (empty($this->templates)) {
            return;
        }

        $newTemplates = $this->templates;
        $this->templates = [];

        try {
            $writer = $this->filesystem->getDirectoryWrite(DirectoryList::LOG);
            $lock = $writer->openFile(self::LOG_FILE . '.lock', 'w');
            $lock->lock();

            try {
                $currentTemplates = [];
                if ($writer->isExist(self::LOG_FILE)) {
                    $currentTemplates = explode("\n", trim($writer->readFile(self::LOG_FILE), " \f\n\r\t\v\x00"));
                }

                $templates = array_filter(array_unique(array_merge($currentTemplates, $newTemplates)));
                sort($templates);
                $writer->writeFile(self::LOG_FILE, implode("\n", $templates) . "\n", 'w');
            } finally {
                $lock->unlock();
                $lock->close();
            }
        } catch (FileSystemException $fileSystemException) {
            return;
        }
    }

    private function getRelativePath(string $template): string
    {
        $root = rtrim($this->directoryList->getRoot(), '/') . '/';
        if (str_starts_with($template, $root)) {
            return substr($template, strlen($root));
        }

        return $template;
    }
}
