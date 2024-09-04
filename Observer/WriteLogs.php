<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;

class WriteLogs implements ObserverInterface
{
    private Config $config;
    private ScriptLogger $scriptLogger;

    public function __construct(
        Config $config,
        ScriptLogger $scriptLogger
    ) {
        $this->config = $config;
        $this->scriptLogger = $scriptLogger;
    }

    public function execute(Observer $observer)
    {
        if ($this->config->logging()) {
            $this->scriptLogger->log();
        }
    }
}
