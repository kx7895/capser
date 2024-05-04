<?php

namespace App\Form\Field;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageFieldType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'row_attr' => [
                'class' => 'form-floating',
            ],
            'class' => Language::class,
            'query_builder' => function (LanguageRepository $repository) {
                return $repository->createQueryBuilder('language')
                    ->orderBy('language.name', 'ASC');
            },
            'choice_label' => 'name',
            'label' => 'Sprache <span class="text-danger">*</span>',
            'label_html' => true,
            'placeholder' => 'Bitte wählen...',
            'autocomplete' => true,
            'tom_select_options' => [
                'hideSelected' => true,
                'plugins' => [
                    'dropdown_input',
                    'clear_button',
                ],
            ],
            'required' => true,
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}