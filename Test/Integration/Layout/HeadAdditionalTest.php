<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 */
class HeadAdditionalTest extends TestCase
{
    public function testHeadAdditionalIsNotWhitelistedByDefault()
    {
        $page = ObjectManager::getInstance()->get(PageFactory::class)->create();
        $page->addHandle('default');

        $block = $page->getLayout()->getBlock('head.additional');
        $this->assertInstanceOf(AbstractBlock::class, $block, 'Block "head.additional" not found');
        $this->assertNotTrue($block->getData('csp_whitelist'));
    }
}
