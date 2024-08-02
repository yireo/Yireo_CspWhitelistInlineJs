<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Logger;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Yireo\CspWhitelistInlineJs\Config\Config;

class ScriptLogger
{
    private DirectoryList $directoryList;
    private Filesystem $filesystem;
    private Config $config;

    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        Config $config
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    public function log(Template $block)
    {
        if (true !== $this->config->logging()) {
            return;
        }

        $logFile = 'yireo-csp-whitelist-inline-js.log';
        $msg = $block->getTemplateFile() . "\n";

        $writer = $this->filesystem->getDirectoryWrite($this->directoryList::LOG);
        if ($writer->isExist()) {
            $writer->writeFile($logFile, $msg, 'a+');
            return;
        }

        $writer->writeFile($logFile, $msg, 'w+');
    }
}
