<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Principal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

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
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Customer::class);
        $this->logger = $logger;
    }

    /** @noinspection PhpUnused */
    public function findBySearch(?string $query, Collection $allowedPrincipals, array $queryParameters, ?string $sort = null, ?string $direction = 'ASC'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->innerJoin('c.principal', 'p')
            ->where('p IN (:allowedPrincipals)')
            ->setParameter('allowedPrincipals', $allowedPrincipals);

        if($query) {
            $qb
                ->leftJoin('c.addressLineCountry', 'addressLineCountry')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like('c.name', ':queryLike'),
                    $qb->expr()->like('c.shortName', ':queryLike'),
                    $qb->expr()->like('c.addressLine1', ':queryLike'),
                    $qb->expr()->like('c.addressLine2', ':queryLike'),
                    $qb->expr()->like('c.addressLine3', ':queryLike'),
                    $qb->expr()->like('c.addressLine4', ':queryLike'),
                    $qb->expr()->like('c.vatId', ':queryLike'),
                    $qb->expr()->like('addressLineCountry.name', ':queryLike'),
                    $qb->expr()->eq('c.id', ':queryExact'),
                    $qb->expr()->eq('c.ledgerAccountNumber', ':queryExact'),
                ))
                ->setParameter('queryLike', '%'.$query.'%')
                ->setParameter('queryExact', $query) ;
        }

        // Nur für bestimmte Such-Parameter gibt es eine Definition, ansonsten wird schlicht nichts angewandt
        foreach($queryParameters as $field => $value) {
            if($field == 'principal') {
                $qb->andWhere('c.principal = :principal')
                    ->setParameter(':principal', $value);
            }
        }

        if($sort) {
            $qb->orderBy('c.' . $sort, $direction);
        }

        return $qb;
    }

    public function findAllowed(Collection $allowedPrincipals): array
    {
        $qb = $this->createQueryBuilder('customer')
            ->innerJoin('customer.principal', 'principal') // Verknüpfung mit der Principal-Entity
            ->where('principal IN (:allowedPrincipals)') // Bedingung, um nur erlaubte Principals zu berücksichtigen
            ->setParameter(':allowedPrincipals', $allowedPrincipals)
            ->orderBy('principal.name', 'ASC')
            ->addOrderBy('customer.name', 'ASC')
            ->getQuery();

        return $qb->getResult();
    }

    public function getNextAvailableCustomerNumber(Principal $principal): ?int
    {
        $customerNumberRange = $principal->getCustomerNumberRange();

        if(!$customerNumberRange) {
            $this->logger->warning('CustomerRepository->getNextAvailableCustomerNumber: Kein $customerNumberRange für Mandant #{id} definiert (NULL)', ['id' => $principal->getId()]);
            return null;
        }

        $qb = $this->createQueryBuilder('customer');
        $qb->select('MAX(customer.ledgerAccountNumber) AS max')
            ->join('customer.principal', 'principal')
            ->where($qb->expr()->eq('principal.id', ':principal'))
            ->andWhere($qb->expr()->like('customer.ledgerAccountNumber', ':customerNumberRange'))
            ->setParameter('principal', $principal)
            ->setParameter('customerNumberRange', $customerNumberRange.'%');
        try {
            $result = $qb->getQuery()->getSingleResult();
            $this->logger->debug('CustomerRepository->getNextAvailableCustomerNumber: Relevanter Eintrag zu {customerNumberRange} für Mandant #{id} gefunden, MAX: {max}', ['customerNumberRange' => $customerNumberRange, 'id' => $principal->getId(), 'max' => $result['max']] );
        } catch (NoResultException|NonUniqueResultException) {
            $this->logger->info('CustomerRepository->getNextAvailableCustomerNumber: Kein relevanter Eintrag zu {customerNumberRange} für Mandant #{id} gefunden', ['customerNumberRange' => $customerNumberRange, 'id' => $principal->getId()] );
            return null;
        }
        if($result['max'] == null)
            return $customerNumberRange.'001';
        else
            return $result['max']+1;
    }
}
