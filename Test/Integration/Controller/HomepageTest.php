<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * @magentoAppArea frontend
 */
class HomepageTest extends AbstractController
{
    private const LOG_FILE = 'yireo-csp-whitelist-inline-js.log';

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLogDirectory()->delete(self::LOG_FILE);
    }

    protected function tearDown(): void
    {
        $this->getLogDirectory()->delete(self::LOG_FILE);
        parent::tearDown();
    }

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode all
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/logging 1
     */
    public function testWritesLogWithTemplatesWhenLoggingIsEnabled()
    {
        $this->dispatch('/');
        $this->assertResponseIsOk();

        $logDirectory = $this->getLogDirectory();
        $this->assertTrue($logDirectory->isExist(self::LOG_FILE), 'Log file was not written');
        $this->assertStringContainsString('.phtml', $logDirectory->readFile(self::LOG_FILE));
    }

    /**
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/mode all
     * @magentoConfigFixture current_store csp_whitelist_inline_js/settings/logging 0
     */
    public function testWritesNoLogWhenLoggingIsDisabled()
    {
        $this->dispatch('/');
        $this->assertResponseIsOk();

        $this->assertFalse($this->getLogDirectory()->isExist(self::LOG_FILE));
    }

    private function assertResponseIsOk(): void
    {
        $response = $this->getResponse();
        $this->assertInstanceOf(HttpResponse::class, $response);
        $this->assertSame(200, $response->getHttpResponseCode());
    }

    private function getLogDirectory(): WriteInterface
    {
        return $this->_objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::LOG);
    }
}
