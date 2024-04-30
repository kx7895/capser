<?php

namespace App\Repository;

use App\Entity\CustomerInvoiceRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerInvoiceRecipient>
 *
 * @method CustomerInvoiceRecipient|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerInvoiceRecipient|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerInvoiceRecipient[]    findAll()
 * @method CustomerInvoiceRecipient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerInvoiceRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerInvoiceRecipient::class);
    }

}
