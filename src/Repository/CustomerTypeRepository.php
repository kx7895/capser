<?php

namespace App\Repository;

use App\Entity\CustomerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerType>
 *
 * @method CustomerType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerType[]    findAll()
 * @method CustomerType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerType::class);
    }

}
