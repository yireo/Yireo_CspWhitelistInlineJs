<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Plugin;

use Magento\Framework\View\Element\Template;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Plugin\AddCspInlineScripts;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertInterceptorPluginIsRegistered;

/**
 * @magentoAppArea frontend
 */
class PluginRegistrationTest extends TestCase
{
    use AssertInterceptorPluginIsRegistered;

    public function testPluginIsRegisteredOnTemplateBlocks()
    {
        $this->assertInterceptorPluginIsRegistered(
            Template::class,
            AddCspInlineScripts::class,
            'Yireo_CspWhitelistInlineJs::addCspInlineScripts'
        );
    }
}
