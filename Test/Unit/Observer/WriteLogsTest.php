<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Config;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;
use Yireo\CspWhitelistInlineJs\Observer\WriteLogs;

class WriteLogsTest extends TestCase
{
    public function testWritesLogsWhenLoggingIsEnabled(): void
    {
        $scriptLogger = $this->createMock(ScriptLogger::class);
        $scriptLogger->expects($this->once())->method('log');

        (new WriteLogs($this->getConfig(true), $scriptLogger))->execute(new Observer());
    }

    public function testSkipsLogsWhenLoggingIsDisabled(): void
    {
        $scriptLogger = $this->createMock(ScriptLogger::class);
        $scriptLogger->expects($this->never())->method('log');

        (new WriteLogs($this->getConfig(false), $scriptLogger))->execute(new Observer());
    }

    private function getConfig(bool $logging): Config
    {
        $config = $this->createMock(Config::class);
        $config->method('logging')->willReturn($logging);

        return $config;
    }
}
