<?php

namespace App\Form;

use App\Entity\InvoiceAttachment;
use App\Form\Field\UploadPdfType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceAttachmentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('upload', UploadPdfType::class, [
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Hochladen',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoiceAttachment::class,
        ]);
    }
}
