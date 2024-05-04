<?php

namespace App\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomDateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'row_attr' => [
                'class' => 'form-floating',
            ],
            'widget' => 'single_text',
            'label' => 'Datum <span class="text-danger">*</span>',
            'label_html' => true,
        ]);
    }

    public function getParent(): string
    {
        return DateType::class;
    }
}