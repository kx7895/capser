<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\InvoicePosition;
use App\Entity\Unit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoicePositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', NumberType::class, [
                'label' => 'Sortierung',
                'attr' => [
                    'class' => 'h-100',
                ],
                'required' => false,
                'scale' => 0,
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Beschreibung',
                'attr' => [
                    'class' => 'h-100',
                ]
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'label' => 'Anzahl',
                'attr' => [
                    'data-action'=>'form-invoice-position-calculator#refresh',
                    'class' => 'h-100'
                ]
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er
                        ->createQueryBuilder('unit')
                        ->andWhere('unit.principal = :principal')
                        ->setParameter('principal', $options['principal'])
                        ->orderBy('unit.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => 'Einheit',
                'attr' => [
                    'class' => 'h-100',
                ],
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'currency' => false,
                'scale' => 2,
                'label' => 'Preis',
                'attr' => [
                    'inputmode' => 'decimal',
                    'data-action'=>'form-invoice-position-calculator#refresh',
                    'class' => 'h-100'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoicePosition::class,
            'principal' => null,
        ]);
    }
}
