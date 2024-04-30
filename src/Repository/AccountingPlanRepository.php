<?php

namespace App\Repository;

use App\Entity\AccountingPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountingPlan>
 *
 * @method AccountingPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountingPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountingPlan[]    findAll()
 * @method AccountingPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingPlan::class);
    }

}
