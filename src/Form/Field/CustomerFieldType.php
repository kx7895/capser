<?php

namespace App\Form\Field;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerFieldType extends AbstractType
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
            'class' => Customer::class,
            'query_builder' => function (CustomerRepository $repository) use ($allowedPrincipals) {
                return $repository->createQueryBuilder('customer')
                    ->join('customer.principal', 'principal')
                    ->andWhere('principal IN (:allowedPrincipals)')
                    ->orderBy('principal.name', 'ASC')
                    ->addOrderBy('customer.name', 'ASC')
                    ->setParameter('allowedPrincipals', $allowedPrincipals);
            },
            'choice_label' =>  function (Customer $customer) {
                return $customer->getPrincipal()->getShortName().' » '.$customer->getName();
            },
            'label' => 'Kunde <span class="text-danger">*</span>',
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