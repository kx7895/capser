<?php

namespace App\Form;

use App\Entity\AccountingPlanLedger;
use App\Entity\Currency;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoiceType;
use App\Entity\Principal;
use App\Entity\TermOfPayment;
use App\Form\Field\AccountingPlanLedgerFieldType;
use App\Form\Field\CustomDateType;
use App\Form\Field\CustomerFieldType;
use App\Form\Field\LanguageFieldType;
use App\Form\Field\PrincipalFieldType;
use App\Form\Field\TermOfPaymentFieldType;
use App\Repository\AccountingPlanLedgerRepository;
use App\Repository\CurrencyRepository;
use App\Repository\InvoiceTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class InvoiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        /* RECHNUNGSSTELLER */
        /* Mandant */
        $builder
            ->add('principal', PrincipalFieldType::class);
        /* Buchungskonto */
        $builder
            ->addDependent('accountingPlanLedger', 'principal', function(DependentField $field, ?Principal $principal) {
                if($principal === null)
                    $field->add(TextType::class, [
                        'label' => 'Buchungskonto',
                        'label_html' => true,
                        'row_attr' => [
                            'class' => 'form-floating',
                        ],
                        'disabled' => true,
                    ]);
                else
                    $field->add(AccountingPlanLedgerFieldType::class, [
                        'selectedPrincipal' => $principal,
                        'choice_label' =>  function (AccountingPlanLedger $entity) {
                            return $entity->getName().' ('.$entity->getNumber().')';
                        },
                        'required' => false,
                    ]);
            });

        /* RECHNUNGSEMPFÄNGER */
        /* Kunde */
        $builder
            ->addDependent('customer', 'principal', function(DependentField $field, ?Principal $principal) {
                if($principal === null)
                    $field->add(TextType::class, [
                        'label' => 'Kunde <span class="text-muted">*</span>',
                        'label_html' => true,
                        'row_attr' => [
                            'class' => 'form-floating',
                        ],
                        'disabled' => true,
                    ]);
                else
                    $field
                        ->add(CustomerFieldType::class, [
                            'selectedPrincipal' => $principal,
                            'choice_label' =>  function (Customer $entity) {
                                return $entity;
                            },
                        ]);
            });
        /* Sprache, Kostenstelle (extern), Referenz (extern) */
        $builder
            ->add('language', LanguageFieldType::class, [
                'label' => 'Belegsprache <span class="text-danger">*</span>',
            ])
            ->add('costcenterExternal', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Kostenstelle (extern)',
                'required' => false,
            ])
            ->add('referenceExternal' , TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Referenz (extern)',
                'required' => false,
            ]);

        /* RECHNUNGSKOPF */
        $builder
            ->add('invoiceType', EntityType::class, [
                'class' => InvoiceType::class,
                'query_builder' => function (InvoiceTypeRepository $repository) {
                    return $repository->createQueryBuilder('invoiceType')
                        ->andWhere('invoiceType.type IN (:availableInvoiceTypes)')
                        ->setParameter('availableInvoiceTypes', ['IN', 'RE', 'CR', 'GU'])
                        ->orderBy('invoiceType.name', 'DESC');
                },
                'choice_label' => 'name',
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'choice_attr' => function() { return ['class' => 'btn-check']; },
                'label_attr' => [ 'class' => 'btn btn-light me-1' ],
            ])
            ->add('date', CustomDateType::class)
            ->add('periodFrom', CustomDateType::class, [
                'label' => 'Zeitraum von <span class="text-danger">*</span>',
            ])
            ->add('periodTo', CustomDateType::class, [
                'label' => 'Zeitraum bis <span class="text-danger">*</span>',
            ])
            ->add('introText', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:120px;'
                ],
                'required' => false,
                'label' => 'Einleitungstext'
            ]);

        /* RECHNUNGSSTAMM */
        $builder
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->createQueryBuilder('currency')
                        ->orderBy('currency.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => false,
                'expanded' => true,
                'choice_attr' => function() { return ['class' => 'btn-check']; },
                'label_attr' => [ 'class' => 'btn btn-light me-1', ],
                'required' => true,
            ])
            ->add('vatType', ChoiceType::class, [
                'choices' => Invoice::VATTYPES,
                'label' => false,
                'expanded' => true,
                'choice_attr' => function() { return ['class' => 'btn-check']; },
                'label_attr' => ['class' => 'btn btn-light me-1 mb-2', ],
                'required' => true,
            ])
            ->add('vatRate', ChoiceType::class, [
                'choices' => Invoice::VATRATES,
                'label' => false,
                'expanded' => true,
                'choice_attr' => function() { return ['class' => 'btn-check']; },
                'label_attr' => ['class' => 'btn btn-light me-1 mb-2', ],
                'required' => true,
            ]);

        /* RECHNUNGSFUSS */
        /* Einleitungstext */
        $builder
            ->add('outroText', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:120px;'
                ],
                'required' => false,
                'label' => 'Nachbemerkung'
            ]);
        /* Zahlungsbedingung */
        $builder
            ->addDependent('termOfPayment', 'principal', function(DependentField $field, ?Principal $principal) {
                if($principal === null)
                    $field->add(TextType::class, [
                        'label' => 'Zahlungsbedingung <span class="text-muted">*</span>',
                        'label_html' => true,
                        'row_attr' => [
                            'class' => 'form-floating',
                        ],
                        'disabled' => true,
                    ]);
                else
                    $field
                        ->add(TermOfPaymentFieldType::class, [
                            'selectedPrincipal' => $principal,
                            'choice_label' =>  function (TermOfPayment $entity) {
                                return $entity->getName().' ('.$entity->getDueDays().' Tage)';
                            },
                        ]);
            });

        $builder
            ->add('invoiceReference', EntityType::class, [
                'class' => Invoice::class,
                'required' => false,
            ])
;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
