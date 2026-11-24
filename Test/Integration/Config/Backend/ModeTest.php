<?php declare(strict_types=1);

namespace Yireo\CspWhitelistInlineJs\Test\Integration\Config\Backend;

use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Yireo\CspWhitelistInlineJs\Config\Backend\Mode;

/**
 * @magentoAppArea adminhtml
 */
class ModeTest extends TestCase
{
    /**
     * @dataProvider getValidValues
     */
    public function testAcceptsValidMode(string $value)
    {
        $mode = $this->getMode($value);
        $mode->beforeSave();
        $this->assertSame($value, $mode->getValue());
    }

    public static function getValidValues(): array
    {
        return [['custom'], ['all']];
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testRejectsInvalidMode(string $value)
    {
        $this->expectException(LocalizedException::class);
        $this->getMode($value)->beforeSave();
    }

    public static function getInvalidValues(): array
    {
        return [[''], ['ALL'], ['foobar'], ['all ']];
    }

    public function testModeFieldUsesBackendModel()
    {
        $structure = ObjectManager::getInstance()->get(Structure::class);
        $field = $structure->getElement('csp_whitelist_inline_js/settings/mode');
        $this->assertSame(Mode::class, $field->getData()['backend_model'] ?? null);
        $this->assertFalse((bool)($field->getData()['showInStore'] ?? false));
        $this->assertFalse((bool)($field->getData()['showInWebsite'] ?? false));
    }

    private function getMode(string $value): Mode
    {
        $mode = ObjectManager::getInstance()->create(Mode::class);
        $mode->setPath('csp_whitelist_inline_js/settings/mode');
        $mode->setValue($value);

        return $mode;
    }
}
