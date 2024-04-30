<?php

namespace App\Repository;

use App\Entity\CustomerContactPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerContactPerson>
 *
 * @method CustomerContactPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerContactPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerContactPerson[]    findAll()
 * @method CustomerContactPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerContactPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerContactPerson::class);
    }

}
