<?php

namespace App\Form\Field;

use App\Entity\AccountingPlanLedger;
use App\Entity\Principal;
use App\Entity\User;
use App\Repository\AccountingPlanLedgerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountingPlanLedgerFieldType extends AbstractType
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
            'class' => AccountingPlanLedger::class,
            'selectedPrincipal' => null,
            'choice_label' =>  function (AccountingPlanLedger $accountingPlanLedger) {
                return $accountingPlanLedger->getAccountingPlanGroup()->getAccountingPlan()->getPrincipal()->getShortName().' » '.$accountingPlanLedger->getName().' (#'.$accountingPlanLedger->getNumber().')';
            },
            'label' => 'Buchungskonto <span class="text-danger">*</span>',
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
            $repository = $options['em']->getRepository(AccountingPlanLedger::class);

            return $this->createAccountingPlanLedgerQueryBuilder($repository, $allowedPrincipals, $options['selectedPrincipal']);
        });
    }

    private function createAccountingPlanLedgerQueryBuilder(AccountingPlanLedgerRepository $repository, Collection $allowedPrincipals, ?Principal $principal): QueryBuilder
    {
        $qb = $repository->createQueryBuilder('aPL')
            ->join('aPL.accountingPlanGroup', 'aPLG')
            ->join('aPLG.accountingPlan', 'aP')
            ->join('aP.principal', 'p')
            ->andWhere('p IN (:allowedPrincipals)')
            ->orderBy('p.name', 'ASC')
            ->addOrderBy('aPL.name', 'ASC')
            ->setParameter('allowedPrincipals', $allowedPrincipals);

        if($principal) {
            $qb->andWhere('p = :principal')
                ->setParameter('principal', $principal);
        }

        return $qb;
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}