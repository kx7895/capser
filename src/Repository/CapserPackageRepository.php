<?php

namespace App\Repository;

use App\Entity\CapserPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CapserPackage>
 *
 * @method CapserPackage|null find($id, $lockMode = null, $lockVersion = null)
 * @method CapserPackage|null findOneBy(array $criteria, array $orderBy = null)
 * @method CapserPackage[]    findAll()
 * @method CapserPackage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CapserPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CapserPackage::class);
    }

}
