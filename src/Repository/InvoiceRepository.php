<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function findBySearch(?string $query, array $queryParameters, ?string $sort = null, ?string $direction = 'ASC'): QueryBuilder
    {

        $qb = $this->createQueryBuilder('c');

        if($query) {
            $qb->andWhere('c.number LIKE :queryLike')
                ->orWhere('c.id = :queryExact')
                ->orWhere('customer.name LIKE :queryLike')
                ->orWhere('customer.shortName LIKE :queryLike')
                ->setParameter('queryLike', '%' . $query . '%')
                ->setParameter('queryExact', $query)
                ->join('c.customer', 'customer');
        }

        // Nur für bestimmte Such-Parameter gibt es eine Definition, ansonsten wird schlicht nichts angewandt
        foreach($queryParameters as $field => $value) {
            if($field == 'principal') {
                $qb->andWhere('c.principal = :principal')
                    ->setParameter(':principal', $value);
            } elseif($field == 'customer') {
                $qb->andWhere('c.customer = :customer')
                    ->setParameter(':customer', $value);
            }
        }

        if($sort)
            $qb->orderBy('c.'.$sort, $direction);

        return $qb;
    }

    /**
     * @throws Exception
     */
    public function getNextAvailableDocumentNumber(int $fibuDocumentNumberRange): int
    {
        $qb = $this->createQueryBuilder('invoice');
        $qb->select('MAX(invoice.number) AS max')
            ->where($qb->expr()->like('invoice.number', ':fibuDocumentNumberRange'))
            ->setParameter('fibuDocumentNumberRange', $fibuDocumentNumberRange.'%');
        try {
            $result = $qb->getQuery()->getSingleResult();
        } catch (NoResultException|NonUniqueResultException) {
            throw new Exception('Nächste Number zur InvoiceNumberRange '.$fibuDocumentNumberRange.' konnte nicht bestimmt werden. [InvoiceRepository:GN1]', 500);
        }
        if($result['max'] == null)
            return $fibuDocumentNumberRange.'0001';
        else
            return $result['max']+1;
    }

}
