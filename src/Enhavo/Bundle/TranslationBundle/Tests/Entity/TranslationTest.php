<?php


namespace Enhavo\Bundle\TranslationBundle\Tests\Entity;


use Enhavo\Bundle\TranslationBundle\Entity\Translation;
use Enhavo\Bundle\TranslationBundle\Tests\Mocks\TranslatableMock;
use PHPUnit\Framework\TestCase;

class TranslationTest extends TestCase
{
    public function testGettersSetters()
    {
        $translatable = new TranslatableMock();
        $translation = new Translation();
        $translation->setRefId(1);
        $translation->setClass(TranslatableMock::class);
        $translation->setLocale('_de');
        $translation->setProperty('_prop');
        $translation->setObject($translatable);
        $this->assertEquals(1, $translation->getRefId());
        $this->assertEquals(TranslatableMock::class, $translation->getClass());
        $this->assertEquals('_de', $translation->getLocale());
        $this->assertEquals('_prop', $translation->getProperty());
        $this->assertEquals($translatable, $translation->getObject());

    }
}
