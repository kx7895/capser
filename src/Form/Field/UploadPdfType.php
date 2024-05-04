<?php

namespace App\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadPdfType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'constraints' => [
                new File([
                    'mimeTypes' => [
                        'application/pdf'
                    ],
                    'mimeTypesMessage' => 'Bitte wählen Sie ein gültiges PDF-Dokument aus.',
                ])
            ],
            'label' => 'Datei <span class="text-danger">*</span>',
            'label_html' => true,
            'help' => 'Die Datei muss im PDF-Format vorliegen.',
        ]);
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}