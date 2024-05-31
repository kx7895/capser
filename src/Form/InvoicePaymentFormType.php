<?php

namespace App\Form;

use App\Entity\AccountingPlanLedger;
use App\Entity\Currency;
use App\Entity\InvoicePayment;
use App\Form\Field\CustomDateType;
use App\Repository\AccountingPlanLedgerRepository;
use App\Repository\CurrencyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoicePaymentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', CustomDateType::class, [
                'label' => 'Zahlungseingang <span class="text-danger">*</span>',
                'required' => true,
            ])
            ->add('amount', NumberType::class, [
                'scale' => 2,
                'label' => 'Bezahlter Betrag <span class="text-danger">*</span>',
                'label_html' => true,
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'required' => true,
            ])
            ->add('currency', EntityType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'class' => Currency::class,
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->createQueryBuilder('currency')
                        ->orderBy('currency.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => 'Währung <span class="text-danger">*</span>',
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
            ])
            /* TODO: Nur eigene Konten und nur vom tatsächlich aktivierten Kontenplan */
            ->add('accountingPlanLedger', EntityType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'class' => AccountingPlanLedger::class,
                'query_builder' => function (AccountingPlanLedgerRepository $repository) {
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => 'Konto <span class="text-danger">*</span>',
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
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => [
                    'class' => 'btn btn-outline-primary btn-sm',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvoicePayment::class,
        ]);
    }
}
