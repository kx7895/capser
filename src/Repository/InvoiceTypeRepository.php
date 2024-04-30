<?php

namespace App\Repository;

use App\Entity\InvoiceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceType>
 *
 * @method InvoiceType|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceType|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceType[]    findAll()
 * @method InvoiceType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceType::class);
    }

}
