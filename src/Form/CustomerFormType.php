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
use App\Form\Field\AccountingPlanLedgerFieldType;
use App\Form\Field\PrincipalFieldType;
use App\Form\Field\TermOfPaymentFieldType;
use App\Repository\CountryRepository;
use App\Repository\CurrencyRepository;
use App\Repository\CustomerRepository;
use App\Repository\CustomerTypeRepository;
use App\Repository\LanguageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class CustomerFormType extends AbstractType
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('principal', PrincipalFieldType::class)
            ->addDependent('ledgerAccountNumber', 'principal', function(DependentField $field, ?Principal $principal) use ($builder, $options) {
                if($principal === null)
                    $field->add(IntegerType::class, [
                        'label' => 'Kundenummer',
                        'row_attr' => ['class' => 'form-floating'],
                        'disabled' => true,
                    ]);
                else {
                    if(!$builder->getData()->getLedgerAccountNumber())
                        $nextAvailableCustomerNumber = $this->customerRepository->getNextAvailableCustomerNumber($principal);
                    else
                        $nextAvailableCustomerNumber = null;
                    $field
                        ->add(IntegerType::class, [
                            'label' => 'Kundenummer <span class="text-danger">*</span>',
                            'label_html' => true,
                            'row_attr' => ['class' => 'form-floating'],
                            'help' => ($nextAvailableCustomerNumber ? 'Nächste freie Kundennummer: <b>'.$nextAvailableCustomerNumber.'</b>' : null),
                            'help_html' => true,
                            'required' => true
                        ]);
                }
            })
            ->addDependent('termOfPaymentDefault', 'principal', function(DependentField $field, ?Principal $principal) {
                if($principal === null)
                    $field->add(TextType::class, [
                        'label' => 'Zahlungsbedingung',
                        'row_attr' => ['class' => 'form-floating'],
                        'disabled' => true,
                    ]);
                else
                    $field
                        ->add(TermOfPaymentFieldType::class, [
                            'label' => 'Zahlungsbedingung',
                            'label_html' => false,
                            'selectedPrincipal' => $principal,
                            'choice_label' =>  function (TermOfPayment $entity) {
                                return $entity->getName().' ('.$entity->getDueDays().' Tage)';
                            },
                            'required' => false,
                        ]);
            })
            ->addDependent('accountingPlanLedgerDefault', 'principal', function(DependentField $field, ?Principal $principal) {
                if($principal === null)
                    $field->add(TextType::class, [
                        'label' => 'Buchungskonto',
                        'row_attr' => ['class' => 'form-floating'],
                        'disabled' => true,
                    ]);
                else
                    $field->add(AccountingPlanLedgerFieldType::class, [
                        'label' => 'Buchungskonto',
                        'label_html' => false,
                        'selectedPrincipal' => $principal,
                        'choice_label' =>  function (AccountingPlanLedger $entity) {
                            return $entity->getName().' ('.$entity->getNumber().')';
                        },
                        'required' => false,
                    ]);
            });

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
                'label' => false,
                'choice_attr' => function() {  return ['class' => 'btn-check']; },
                'label_attr' => ['class' => 'btn btn-light me-2', ],
                'required' => true,
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


            // unfortunately, it is necessary to specify the style-height of textarea fixed according to https://getbootstrap.com/docs/5.3/forms/floating-labels/#textareas
            ->add('specialFooterColumn1', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:140px;'
                ],
                'label' => 'Fusszeile 1',
                'required' => false,
            ])
            ->add('specialFooterColumn2', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:140px;'
                ],
                'label' => 'Fusszeile 2',
                'required' => false,
            ])
            ->add('specialFooterColumn3', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:140px;'
                ],
                'label' => 'Fusszeile 3',
                'required' => false,
            ])

            /* INVOICE RECEIVERS */
            ->add('customerInvoiceRecipients', CollectionType::class, [
                'entry_type' => CustomerInvoiceRecipientType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])

            /* TAGS */
            ->add('tags', CollectionType::class, [
                'entry_type' => TagType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])

            ->add('submitXXL', SubmitType::class, [
                'label' => '<i class="fa-regular fa-floppy-disk me-1"></i> Speichern',
                'label_html' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="fa-regular fa-floppy-disk me-1"></i> Speichern',
                'label_html' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class
        ]);
    }
}
