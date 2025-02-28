<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\ChoiceConfigurator;
use function Symfony\Component\Translation\t;

class ChoiceFieldTest extends AbstractFieldTest
{
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->choices = ['a' => 1, 'b' => 2, 'c' => 3];

        $this->configurator = new ChoiceConfigurator();
    }

    public function testFieldWithoutChoices()
    {
        $this->expectException(\InvalidArgumentException::class);

        $field = ChoiceField::new('foo');
        $this->configure($field);
    }

    public function testFieldWithChoiceGeneratorCallback()
    {
        $field = ChoiceField::new('foo')->setChoices(static function () { return ['foo' => 1, 'bar' => 2]; });

        self::assertSame(['foo' => 1, 'bar' => 2], $this->configure($field)->getFormTypeOption(ChoiceField::OPTION_CHOICES));

        $field->setValue(1);
        self::assertSame('foo', (string) $this->configure($field)->getFormattedValue());
    }

    public function testFieldWithTranslatableChoices()
    {
        $field = ChoiceField::new('foo')->setChoices([1 => t('foo'), 2 => 'bar'])->setChoicesTranslatable();

        $field->setValue(1);
        self::assertSame('foo', (string) $this->configure($field)->getFormattedValue());

        $field->setValue(2);
        self::assertSame('bar', (string) $this->configure($field)->getFormattedValue());
    }

    public function testFieldWithWrongVisualOptions()
    {
        $this->expectException(\InvalidArgumentException::class);

        $field = ChoiceField::new('foo')->setChoices($this->choices);
        $field->renderExpanded();
        $field->renderAsNativeWidget(false);
        $this->configure($field);
    }

    public function testDefaultWidget()
    {
        $field = ChoiceField::new('foo')->setChoices($this->choices);

        $field->renderExpanded(false);
        $field->setCustomOption(ChoiceField::OPTION_WIDGET, null);
        self::assertSame(ChoiceField::WIDGET_AUTOCOMPLETE, $this->configure($field)->getCustomOption(ChoiceField::OPTION_WIDGET));

        $field->renderExpanded(true);
        $field->setCustomOption(ChoiceField::OPTION_WIDGET, null);
        $fieldDto = $this->configure($field);
        self::assertSame(ChoiceField::WIDGET_NATIVE, $fieldDto->getCustomOption(ChoiceField::OPTION_WIDGET));
        self::assertSame('ea-autocomplete', $fieldDto->getFormTypeOption('attr.data-ea-widget'));
    }

    public function testFieldFormOptions()
    {
        $field = ChoiceField::new('foo')->setChoices($this->choices);
        $field->renderExpanded();
        $field->allowMultipleChoices();

        self::assertSame(
            [
                'choices' => $this->choices,
                'multiple' => true,
                'expanded' => true,
                'placeholder' => '',
                'attr' => ['data-ea-autocomplete-render-items-as-html' => 'false'],
            ],
            $this->configure($field)->getFormTypeOptions()
        );
    }

    public function testBadges()
    {
        $field = ChoiceField::new('foo')->setChoices($this->choices);

        $field->setValue(1);
        self::assertSame('a', (string) $this->configure($field)->getFormattedValue());

        $field->setValue([1, 3]);
        self::assertSame('a, c', (string) $this->configure($field)->getFormattedValue());

        $field->setValue(1)->renderAsBadges();
        self::assertSame('<span class="badge badge-secondary">a</span>', (string) $this->configure($field)->getFormattedValue());

        $field->setValue([1, 3])->renderAsBadges();
        self::assertSame('<span class="badge badge-secondary">a</span><span class="badge badge-secondary">c</span>', (string) $this->configure($field)->getFormattedValue());

        $field->setValue(1)->renderAsBadges([1 => 'warning', '3' => 'danger']);
        self::assertSame('<span class="badge badge-warning">a</span>', (string) $this->configure($field)->getFormattedValue());

        $field->setValue([1, 3])->renderAsBadges([1 => 'warning', '3' => 'danger']);
        self::assertSame('<span class="badge badge-warning">a</span><span class="badge badge-danger">c</span>', (string) $this->configure($field)->getFormattedValue());

        $field->setValue(1)->renderAsBadges(function ($value) { return $value > 1 ? 'success' : 'primary'; });
        self::assertSame('<span class="badge badge-primary">a</span>', (string) $this->configure($field)->getFormattedValue());

        $field->setValue([1, 3])->renderAsBadges(function ($value) { return $value > 1 ? 'success' : 'primary'; });
        self::assertSame('<span class="badge badge-primary">a</span><span class="badge badge-success">c</span>', (string) $this->configure($field)->getFormattedValue());
    }
}
