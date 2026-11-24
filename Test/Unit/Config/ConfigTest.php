<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Config;

class ConfigTest extends TestCase
{
    public function testReadsModeInDefaultScopeOnlyOnce(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('csp_whitelist_inline_js/settings/mode')
            ->willReturn('all');

        $config = $this->getConfig($scopeConfig);
        $this->assertSame(['all', 'all'], [$config->getMode(), $config->getMode()]);
    }

    /**
     * @dataProvider getModeValues
     */
    public function testModeFallsBackToCustom(?string $value): void
    {
        $this->assertSame('custom', $this->getConfig($this->getScopeConfig($value))->getMode());
    }

    public static function getModeValues(): array
    {
        return [
            [null],
            [''],
            ['custom'],
            ['foobar'],
            ['ALL'],
        ];
    }

    /**
     * @dataProvider getProductionModeValues
     */
    public function testModeAllInProductionModeRequiresDeploymentFlag(mixed $flag, string $expectedMode): void
    {
        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $deploymentConfig->method('get')->with(Config::ALLOW_ALL_IN_PRODUCTION)->willReturn($flag);

        $config = $this->getConfig($this->getScopeConfig('all'), State::MODE_PRODUCTION, $deploymentConfig);
        $this->assertSame($expectedMode, $config->getMode());
    }

    public static function getProductionModeValues(): array
    {
        return [
            [null, 'custom'],
            [false, 'custom'],
            [0, 'custom'],
            [true, 'all'],
            [1, 'all'],
        ];
    }

    public function testModeAllInDeveloperModeDoesNotRequireDeploymentFlag(): void
    {
        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $deploymentConfig->expects($this->never())->method('get');

        $config = $this->getConfig($this->getScopeConfig('all'), State::MODE_DEVELOPER, $deploymentConfig);
        $this->assertSame('all', $config->getMode());
    }

    public function testReadsLoggingInStoreScopeOfCurrentStoreOnlyOnce(): void
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('csp_whitelist_inline_js/settings/logging', ScopeInterface::SCOPE_STORE, $store)
            ->willReturn('1');

        $config = $this->getConfig($scopeConfig, State::MODE_DEVELOPER, null, $storeManager);
        $this->assertSame([true, true], [$config->logging(), $config->logging()]);
    }

    public function testLoggingIsDisabledWithoutStore(): void
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willThrowException(new NoSuchEntityException());

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->never())->method('getValue');

        $this->assertFalse($this->getConfig($scopeConfig, State::MODE_DEVELOPER, null, $storeManager)->logging());
    }

    /**
     * @dataProvider getLoggingValues
     */
    public function testLogging(?string $value, bool $expected): void
    {
        $this->assertSame($expected, $this->getConfig($this->getScopeConfig($value))->logging());
    }

    public static function getLoggingValues(): array
    {
        return [
            ['1', true],
            ['0', false],
            [null, false],
        ];
    }

    private function getScopeConfig(?string $value): ScopeConfigInterface
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn($value);

        return $scopeConfig;
    }

    private function getConfig(
        ScopeConfigInterface $scopeConfig,
        string $appMode = State::MODE_DEVELOPER,
        ?DeploymentConfig $deploymentConfig = null,
        ?StoreManagerInterface $storeManager = null
    ): Config {
        if ($storeManager === null) {
            $storeManager = $this->createMock(StoreManagerInterface::class);
            $storeManager->method('getStore')->willReturn($this->createMock(StoreInterface::class));
        }

        $appState = $this->createMock(State::class);
        $appState->method('getMode')->willReturn($appMode);

        return new Config(
            $scopeConfig,
            $storeManager,
            $appState,
            $deploymentConfig ?? $this->createMock(DeploymentConfig::class)
        );
    }
}
