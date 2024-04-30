<?php

namespace App\Repository;

use App\Entity\CompanyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyType>
 *
 * @method CompanyType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyType[]    findAll()
 * @method CompanyType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyType::class);
    }

}
