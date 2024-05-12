<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
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

    public function findBySearch(?string $query, Collection $allowedPrincipals, array $queryParameters, ?string $sort = null, ?string $direction = 'ASC'): QueryBuilder
    {

        $qb = $this->createQueryBuilder('i');

        $qb
            ->innerJoin('i.principal', 'p')
            ->where('p IN (:allowedPrincipals)')
            ->setParameter('allowedPrincipals', $allowedPrincipals);

        if($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('i.id', ':queryExact'),
                    $qb->expr()->like('i.number', ':queryLike'),
                    $qb->expr()->like('i.hCustomerName', ':queryLike'),
                    $qb->expr()->like('i.hCustomerShortName', ':queryLike'),
                ))
                ->setParameter('queryLike', '%'.$query.'%')
                ->setParameter('queryExact', $query) ;
        }

        // Nur für bestimmte Such-Parameter gibt es eine Definition, ansonsten wird schlicht nichts angewandt
        foreach($queryParameters as $field => $value) {
            if($field == 'principal') {
                $qb->andWhere('i.principal = :principal')
                    ->setParameter(':principal', $value);
            } elseif($field == 'customer') {
                $qb->andWhere('i.customer = :customer')
                    ->setParameter(':customer', $value);
            } elseif($field == 'draft') {
                if($value === false) {
                    $qb->andWhere($qb->expr()->isNotNull('i.number'));
                }
            } elseif($field == 'paid') {
                if($value === false) {
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->isNull('i.paymentIsPaid'),
                            $qb->expr()->eq('i.paymentIsPaid', '0')
                        )
                    );
                }
            }
        }

        if($sort)
            $qb->orderBy('i.'.$sort, $direction);

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
