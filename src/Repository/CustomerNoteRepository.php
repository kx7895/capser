<?php

namespace App\Repository;

use App\Entity\CustomerNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerNote>
 *
 * @method CustomerNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerNote[]    findAll()
 * @method CustomerNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerNote::class);
    }
}
