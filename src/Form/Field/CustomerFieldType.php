<?php

namespace App\Form\Field;

use App\Entity\Customer;
use App\Entity\Principal;
use App\Entity\User;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
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
            'selectedPrincipal' => null,
            'choice_label' =>  function (Customer $entity) {
                return $entity->getPrincipal().' » '.$entity;
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

        $resolver->setNormalizer('query_builder', function ($options) use ($allowedPrincipals) {
            $repository = $options['em']->getRepository(Customer::class);

            return $this->createCustomerQueryBuilder($repository, $allowedPrincipals, $options['selectedPrincipal']);
        });
    }

    private function createCustomerQueryBuilder(CustomerRepository $repository, Collection $allowedPrincipals, ?Principal $principal): QueryBuilder
    {
        $qb = $repository->createQueryBuilder('customer')
            ->join('customer.principal', 'principal')
            ->andWhere('principal IN (:allowedPrincipals)')
            ->orderBy('principal.name', 'ASC')
            ->addOrderBy('customer.name', 'ASC')
            ->setParameter('allowedPrincipals', $allowedPrincipals);

        if($principal) {
            $qb->andWhere('customer.principal = :principal')
                ->setParameter('principal', $principal);
        }

        return $qb;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}