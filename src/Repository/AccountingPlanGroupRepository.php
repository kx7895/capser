<?php

namespace App\Repository;

use App\Entity\AccountingPlanGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountingPlanGroup>
 *
 * @method AccountingPlanGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountingPlanGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountingPlanGroup[]    findAll()
 * @method AccountingPlanGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingPlanGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingPlanGroup::class);
    }

}
