<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Form\Type;

use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;
use Enhavo\Bundle\TranslationBundle\Translation\TranslationManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationMemoryType extends AbstractType
{
    public function __construct(
        private readonly string $model,
        private readonly TranslationManager $translationManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
            'label' => 'form.label.status',
            'translation_domain' => 'EnhavoTranslationBundle',
            'choices' => $this->getStatusChoices(),
        ]);

        $builder->add('comment', TextType::class, [
            'label' => 'form.label.comment',
            'translation_domain' => 'EnhavoTranslationBundle',
            'required' => false,
        ]);

        $builder->add('sourceLanguage', HiddenType::class, [
            'empty_data' => $this->translationManager->getDefaultLocale(),
        ]);

        $builder->add('targetLanguage', ChoiceType::class, [
            'label' => 'form.label.target_language',
            'translation_domain' => 'EnhavoTranslationBundle',
            'choices' => $this->getLocaleChoices(),
            'placeholder' => '',
        ]);

        $builder->add('sourceValue', TextareaType::class, [
            'label' => 'form.label.source_value',
            'translation_domain' => 'EnhavoTranslationBundle',
            'required' => false,
            'attr' => ['rows' => 10],
        ]);

        $builder->add('targetValue', TextareaType::class, [
            'label' => 'form.label.target_value',
            'translation_domain' => 'EnhavoTranslationBundle',
            'required' => false,
            'attr' => ['rows' => 10],
        ]);

        $builder->add('usages', TextareaType::class, [
            'label' => 'form.label.usages',
            'translation_domain' => 'EnhavoTranslationBundle',
            'required' => false,
            'attr' => [
                'rows' => 5,
                'readonly' => true,
            ],
        ]);
    }

    /** @return array<string, string> */
    private function getStatusChoices(): array
    {
        $choices = [];
        foreach (TranslationMemoryInterface::STATUSES as $status) {
            $choices['form.label.status_'.$status] = $status;
        }

        return $choices;
    }

    /** @return array<string, string> */
    private function getLocaleChoices(): array
    {
        $choices = [];
        foreach ($this->translationManager->getLocales() as $locale) {
            $choices[$locale] = $locale;
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->model,
        ]);
    }
}
