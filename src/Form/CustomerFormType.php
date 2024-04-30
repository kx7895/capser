<?php

namespace App\Form;

use App\Entity\AccountingPlanLedger;
use App\Entity\Country;
use App\Entity\Currency;
use App\Entity\Customer;
use App\Entity\CustomerType;
use App\Entity\Language;
use App\Entity\Principal;
use App\Entity\TermOfPayment;
use App\Repository\AccountingPlanLedgerRepository;
use App\Repository\CountryRepository;
use App\Repository\CurrencyRepository;
use App\Repository\CustomerTypeRepository;
use App\Repository\LanguageRepository;
use App\Repository\PrincipalRepository;
use App\Repository\TermOfPaymentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /* BASISANGABEN */
            ->add('customerType', EntityType::class, [
                'class' => CustomerType::class,
                'query_builder' => function (CustomerTypeRepository $repository) {
                    return $repository->createQueryBuilder('customerType')
                        ->orderBy('customerType.name', 'DESC');
                },
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'label' => false,
                'attr' => [
                    'data-controller' => 'radio-to-button',
                ],
            ])
            ->add('name', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Name <span class="text-danger">*</span>',
                'label_html' => true,
                'required' => true,
            ])
            ->add('shortName', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Kurzbezeichnung',
                'required' => false
            ])
            ->add('ledgerAccountNumber', IntegerType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Kundenummer laut Kontenplan <span class="text-danger">*</span>',
                'label_html' => true,
                'required' => true
            ])
            // TODO: Nur zeigen, wenn man überhaupt mehr als 1 Principal zugewiesen hat
            ->add('principal', EntityType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'class' => Principal::class,
                'query_builder' => function (PrincipalRepository $repository) { // TODO: Security - nur eigene Principals anzeigen
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'label' => 'Mandant <span class="text-danger">*</span>',
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
            ])
//            ->add('logoPath') // TODO: Upload-Field für Logo integrieren


            /* ANSCHRIFT */
            ->add('addressLine1', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Adresszeile 1 (z.B. Strasse und Hausnummer)',
                'label_html' => true,
                'required' => false,
            ])
            ->add('addressLine2', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Adresszeile 2 (z.B. Postleitzahl und Ort)',
                'label_html' => true,
                'required' => false,
            ])
            ->add('addressLine3', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Adresszeile 3',
                'required' => false,
            ])
            ->add('addressLine4', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Adresszeile 4',
                'required' => false,
            ])
            ->add('addressLineCountry', EntityType::class, [
                'class' => Country::class,
                'query_builder' => function (CountryRepository $repository) {
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Land',
                'label_html' => true,
                'placeholder' => 'Bitte wählen...',
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'hideSelected' => true,
                    'plugins' => [
                        'dropdown_input',
                        'clear_button',
                    ],
                ],
            ])


            /* STEUERLICHE ANGABEN */
            ->add('vatId', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'USt.-Id.-Nr.',
                'required' => false,
            ])
            ->add('vatNumber', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Steuernummer',
                'required' => false,
            ])
            ->add('vatExemptInvoicesAllowed', CheckboxType::class, [
                'label_attr' => [
                    'class' => 'checkbox-switch',
                ],
                'label' => 'Steuerfreie Rechnungen nach Reverse Charge zulässig?',
                'required' => false,
            ])

            /* ZAHLUNGSDATEN */
            ->add('bankAccountHolder', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Kontoinhaber',
                'required' => false,
            ])
            ->add('bankAccountBank', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Institut',
                'required' => false,
            ])
            ->add('bankAccountIban', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'IBAN',
                'required' => false,
            ])
            ->add('bankAccountBic', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'BIC/Swift',
                'required' => false,
            ])
            ->add('bankDirectAuthorizationNumber', TextType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Lastschriftmandat-Referenz',
                'required' => false,
            ])
            ->add('bankDirectAuthorizationDate', DateType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'widget' => 'single_text',
                'attr' => [
                    'data-controller' => 'datepicker',
                ],
                'label' => 'Lastschriftmandat-Unterschrift',
                'required' => false,
            ])


            /* RECHNUNGSSTANDARDS */
            ->add('currencyDefault', EntityType::class, [
                'class' => Currency::class,
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Währung',
                'placeholder' => 'Bitte wählen...',
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'hideSelected' => true,
                    'plugins' => [
                        'dropdown_input',
                        'clear_button',
                    ],
                ],
            ])
            ->add('languageDefault', EntityType::class, [
                'class' => Language::class,
                'query_builder' => function (LanguageRepository $repository) {
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Belegsprache',
                'placeholder' => 'Bitte wählen...',
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'hideSelected' => true,
                    'plugins' => [
                        'dropdown_input',
                        'clear_button',
                    ],
                ],
            ])
            ->add('termOfPaymentDefault', EntityType::class, [
                'class' => TermOfPayment::class,
                'query_builder' => function (TermOfPaymentRepository $repository) { // TODO: Security - nur eigene Pläne anzeigen
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Zahlungsbedingung',
                'placeholder' => 'Bitte wählen...',
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'hideSelected' => true,
                    'plugins' => [
                        'dropdown_input',
                        'clear_button',
                    ],
                ],
            ])
            ->add('accountingPlanLedgerDefault', EntityType::class, [
                'class' => AccountingPlanLedger::class,
                'query_builder' => function (AccountingPlanLedgerRepository $repository) { // TODO: Security - nur eigene Pläne anzeigen
                    return $repository->createQueryBuilder('entity')
                        ->orderBy('entity.name', 'ASC');
                },
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'label' => 'Buchungskonto',
                'placeholder' => 'Bitte wählen...',
                'required' => false,
                'autocomplete' => true,
                'tom_select_options' => [
                    'hideSelected' => true,
                    'plugins' => [
                        'dropdown_input',
                        'clear_button',
                    ],
                ],
            ])


            /* TAGS */
            ->add('tags', CollectionType::class, [
                'entry_type' => TagType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
