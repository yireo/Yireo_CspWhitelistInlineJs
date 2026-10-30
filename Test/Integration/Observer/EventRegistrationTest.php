<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Observer\WriteLogs;

/**
 * @magentoAppArea frontend
 */
class EventRegistrationTest extends TestCase
{
    public function testObserverIsRegisteredBeforeSendingResponse()
    {
        $observers = ObjectManager::getInstance()->get(ConfigInterface::class)
            ->getObservers('controller_front_send_response_before');

        $observerName = 'Yireo_CspWhitelistInlineJs::writeLogs';
        $this->assertArrayHasKey($observerName, $observers);
        $this->assertSame(WriteLogs::class, $observers[$observerName]['instance']);
    }
}
