<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Config;

class ConfigTest extends TestCase
{
    public function testReadsModeInStoreScopeOfCurrentStore(): void
    {
        $store = $this->createMock(StoreInterface::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('csp_whitelist_inline_js/settings/mode', ScopeInterface::SCOPE_STORE, $store)
            ->willReturn('all');

        $this->assertSame('all', (new Config($scopeConfig, $storeManager))->getMode());
    }

    public function testModeDefaultsToCustom(): void
    {
        $this->assertSame('custom', $this->getConfig(null)->getMode());
        $this->assertSame('custom', $this->getConfig('')->getMode());
    }

    public function testFallsBackToDefaultsWithoutStore(): void
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willThrowException(new NoSuchEntityException());

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->never())->method('getValue');

        $config = new Config($scopeConfig, $storeManager);
        $this->assertSame('custom', $config->getMode());
        $this->assertFalse($config->logging());
    }

    /**
     * @dataProvider getLoggingValues
     */
    public function testLogging(?string $value, bool $expected): void
    {
        $this->assertSame($expected, $this->getConfig($value)->logging());
    }

    public static function getLoggingValues(): array
    {
        return [
            ['1', true],
            ['0', false],
            [null, false],
        ];
    }

    private function getConfig(?string $value): Config
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($this->createMock(StoreInterface::class));

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($value);

        return new Config($scopeConfig, $storeManager);
    }
}
