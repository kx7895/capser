<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findBySearch(?string $query, array $queryParameters, ?string $sort = null, ?string $direction = 'ASC'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if($query) {
            $qb->andWhere('c.name LIKE :queryLike')
                ->orWhere('c.shortName LIKE :queryLike')
                ->orWhere('c.addressLine1 LIKE :queryLike')
                ->orWhere('c.addressLine2 LIKE :queryLike')
                ->orWhere('c.addressLine3 LIKE :queryLike')
                ->orWhere('c.addressLine4 LIKE :queryLike')
                ->orWhere('addressLineCountry.name LIKE :queryLike')
                ->orWhere('c.ledgerAccountNumber = :queryExact')
                ->orWhere('c.vatId = :queryLike')
                ->orWhere('c.id = :queryExact')
                ->setParameter('queryLike', '%' . $query . '%')
                ->setParameter('queryExact', $query)
                ->join('c.addressLineCountry', 'addressLineCountry');
        }

        // Nur für bestimmte Such-Parameter gibt es eine Definition, ansonsten wird schlicht nichts angewandt
        foreach($queryParameters as $field => $value) {
            if($field == 'principal') {
                $qb->andWhere('c.principal = :principal')
                    ->setParameter(':principal', $value);
            }
        }

        if ($sort) {
            $qb->orderBy('c.' . $sort, $direction);
        }

        return $qb;
    }
}
