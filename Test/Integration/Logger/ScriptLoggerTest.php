<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Logger\ScriptLogger;

/**
 * @magentoAppArea frontend
 */
class ScriptLoggerTest extends TestCase
{
    private const LOG_FILE = 'yireo-csp-whitelist-inline-js.log';

    protected function setUp(): void
    {
        $this->getLogDirectory()->delete(self::LOG_FILE);
    }

    protected function tearDown(): void
    {
        $this->getLogDirectory()->delete(self::LOG_FILE);
    }

    public function testDoesNotAddBlockWithoutTemplate()
    {
        $this->assertFalse($this->getScriptLogger()->add($this->getBlock('')));
    }

    public function testDoesNotAddBlockWithMissingTemplate()
    {
        $this->assertFalse($this->getScriptLogger()->add($this->getBlock('Magento_Theme::does/not/exist.phtml')));
    }

    public function testWritesSortedUniqueTemplatesMergedWithExistingLog()
    {
        $logDirectory = $this->getLogDirectory();
        $logDirectory->writeFile(self::LOG_FILE, "/zzz/existing.phtml\n/aaa/existing.phtml\n");

        $titleBlock = $this->getBlock('Magento_Theme::html/title.phtml');
        $scriptLogger = $this->getScriptLogger();
        $this->assertTrue($scriptLogger->add($titleBlock));
        $this->assertTrue($scriptLogger->add($titleBlock));
        $scriptLogger->log();

        $titleTemplate = $titleBlock->getTemplateFile();
        $templates = [$titleTemplate, '/zzz/existing.phtml', '/aaa/existing.phtml'];
        sort($templates);
        $this->assertSame(implode("\n", $templates) . "\n", $logDirectory->readFile(self::LOG_FILE));

        $logDirectory->writeFile(self::LOG_FILE, "/aaa/existing.phtml\n");
        $scriptLogger->log();
        $this->assertSame("/aaa/existing.phtml\n", $logDirectory->readFile(self::LOG_FILE));
    }

    private function getBlock(string $template): Template
    {
        $layout = ObjectManager::getInstance()->get(LayoutInterface::class);
        /** @var Template $block */
        $block = $layout->createBlock(Template::class, '', ['data' => ['template' => $template]]);
        return $block;
    }

    private function getScriptLogger(): ScriptLogger
    {
        return ObjectManager::getInstance()->create(ScriptLogger::class);
    }

    private function getLogDirectory(): WriteInterface
    {
        return ObjectManager::getInstance()->get(Filesystem::class)->getDirectoryWrite(DirectoryList::LOG);
    }
}
