<?php

namespace App\Form;

use App\Entity\Invoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoicePositionsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('introText', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:100px;'
                ],
                'required' => false,
                'label' => 'Einleitungstext'
            ])
            ->add('outroText', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:100px;'
                ],
                'required' => false,
                'label' => 'Nachbemerkung'
        ])

        ->add('invoicePositions', CollectionType::class, [
            'entry_type' => InvoicePositionType::class,
            'entry_options' => ['label' => false, 'principal' => $options['principal']],
            'allow_add' => true,
            'by_reference' => false,
            'allow_delete' => true,
        ])

        ->add('returnXXL', SubmitType::class, [
            'label' => '<i class="fa-solid fa-chevron-left me-1"></i> Zurück',
            'label_html' => true,
        ])
        ->add('submitXXL', SubmitType::class, [
            'label' => '<i class="fa-regular fa-floppy-disk me-1"></i> Entwurf speichern',
            'label_html' => true,
        ])
        ->add('finalizeXXL', SubmitType::class, [
            'label' => '<i class="fa-solid fa-chevron-right me-1"></i> Abschliessen',
            'label_html' => true,
            'attr' => [
                'onclick' => 'return confirm("Soll dieser Beleg wirklich abgeschlossen werden? Dieser Schritt kann nicht rückgängig gemacht werden.")',
            ]
        ])

        ->add('return', SubmitType::class, [
            'label' => '<i class="fa-solid fa-chevron-left me-1"></i> Zurück',
            'label_html' => true,
        ])
        ->add('submit', SubmitType::class, [
            'label' => '<i class="fa-regular fa-floppy-disk me-1"></i> Entwurf speichern',
            'label_html' => true,
        ])
        ->add('finalize', SubmitType::class, [
            'label' => '<i class="fa-solid fa-chevron-right me-1"></i> Abschliessen',
            'label_html' => true,
            'attr' => [
                'onclick' => 'return confirm("Soll dieser Beleg wirklich abgeschlossen werden? Dieser Schritt kann nicht rückgängig gemacht werden.")',
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
            'principal' => null,
        ]);
    }
}
