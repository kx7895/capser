<?php

namespace App\Form\Field;

use App\Entity\TermOfPayment;
use App\Entity\User;
use App\Repository\TermOfPaymentRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TermOfPaymentFieldType extends AbstractType
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $allowedPrincipals = $this->getAllowedPrincipals();

        $resolver->setDefaults([
            'row_attr' => [
                'class' => 'form-floating',
            ],
            'class' => TermOfPayment::class,
            'query_builder' => function (TermOfPaymentRepository $repository) use ($allowedPrincipals) {
                return $repository->createQueryBuilder('termOfPayment')
                    ->join('termOfPayment.principal', 'principal')
                    ->andWhere('principal IN (:allowedPrincipals)')
                    ->orderBy('principal.name', 'ASC')
                    ->addOrderBy('termOfPayment.name', 'ASC')
                    ->setParameter('allowedPrincipals', $allowedPrincipals);
            },
            'choice_label' =>  function (TermOfPayment $termOfPayment) {
                return $termOfPayment->getPrincipal()->getShortName().' » '.$termOfPayment->getName().' ('.$termOfPayment->getDueDays().' Tage)';
            },
            'label' => 'Zahlungsbedingung <span class="text-danger">*</span>',
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
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}