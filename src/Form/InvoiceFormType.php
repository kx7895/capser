<?php

namespace App\Form;

use App\Entity\AccountingPlanLedger;
use App\Entity\Currency;
use App\Entity\Invoice;
use App\Entity\InvoiceType;
use App\Entity\User;
use App\Form\Field\CustomDateType;
use App\Form\Field\CustomerFieldType;
use App\Form\Field\LanguageFieldType;
use App\Form\Field\PrincipalFieldType;
use App\Form\Field\TermOfPaymentFieldType;
use App\Repository\CurrencyRepository;
use App\Repository\InvoiceTypeRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceFormType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    ) {}

    private function getAllowedPrincipals(): Collection
    {
        /** @var User $user */
        $user = $this->security->getUser();
        return $user->getPrincipals();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /* RECHNUNGSSTELLER */
            ->add('principal', PrincipalFieldType::class)
            ->add('accountingPlanLedger', EntityType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'class' => AccountingPlanLedger::class,
                'choice_label' => 'id',
                'label' => 'Buchungskonto',
                'required' => false,
            ])

            /* RECHNUNGSEMPFÄNGER */
            ->add('customer', CustomerFieldType::class)
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
            ])

            /* RECHNUNGSKOPF */
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
                'attr' => [
                    'data-controller' => 'radio-to-button',
                ],
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
            ])

            /* RECHNUNGSSTAMM */
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
                'query_builder' => function (CurrencyRepository $repository) {
                    return $repository->createQueryBuilder('currency')
                        ->orderBy('currency.name', 'ASC');
                },
                'choice_label' => 'name',
                'label' => 'Währung <span class="text-danger">*</span>',
                'label_html' => true,
                'expanded' => true,
                'attr' => [
                    'data-controller' => 'radio-to-button',
                ],
            ])
            ->add('vatRate', ChoiceType::class, [
                'choices' => Invoice::VATRATES,
                'expanded' => true,
                'label' => 'MwSt-Satz <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => [
                    'data-controller' => 'radio-to-button',
                ],
            ])
            ->add('vatType', ChoiceType::class, [
                'choices' => Invoice::VATTYPES,
                'expanded' => true,
                'label' => 'MwSt-Art <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => [
                    'data-controller' => 'radio-to-button',
                ],
            ])

            /* RECHNUNGSFUSS */
            ->add('outroText', TextareaType::class, [
                'row_attr' => [
                    'class' => 'form-floating',
                ],
                'attr' => [
                    'style' => 'height:120px;'
                ],
                'required' => false,
                'label' => 'Nachbemerkung'
            ])
            ->add('termOfPayment', TermOfPaymentFieldType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
