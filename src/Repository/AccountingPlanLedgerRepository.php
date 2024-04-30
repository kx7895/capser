<?php

namespace App\Repository;

use App\Entity\AccountingPlanLedger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccountingPlanLedger>
 *
 * @method AccountingPlanLedger|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountingPlanLedger|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountingPlanLedger[]    findAll()
 * @method AccountingPlanLedger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountingPlanLedgerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountingPlanLedger::class);
    }

}
