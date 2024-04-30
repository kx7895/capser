<?php

namespace App\Repository;

use App\Entity\InvoiceNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceNote>
 *
 * @method InvoiceNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceNote[]    findAll()
 * @method InvoiceNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceNote::class);
    }

}
