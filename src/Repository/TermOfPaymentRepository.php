<?php

namespace App\Repository;

use App\Entity\TermOfPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TermOfPayment>
 *
 * @method TermOfPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TermOfPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TermOfPayment[]    findAll()
 * @method TermOfPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TermOfPaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TermOfPayment::class);
    }

}
