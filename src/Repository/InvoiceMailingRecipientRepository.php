<?php

namespace App\Repository;

use App\Entity\InvoiceMailingRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceMailingRecipient>
 *
 * @method InvoiceMailingRecipient|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceMailingRecipient|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceMailingRecipient[]    findAll()
 * @method InvoiceMailingRecipient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceMailingRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceMailingRecipient::class);
    }

}
