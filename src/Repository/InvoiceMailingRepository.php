<?php

namespace App\Repository;

use App\Entity\InvoiceMailing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceMailing>
 *
 * @method InvoiceMailing|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceMailing|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceMailing[]    findAll()
 * @method InvoiceMailing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceMailingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceMailing::class);
    }

}
