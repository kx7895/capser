<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\Principal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

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
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Invoice::class);
        $this->logger = $logger;
    }

    public function findBySearch(?string $query, Collection $allowedPrincipals, array $queryParameters, ?string $sort = null, ?string $sortDirection = 'ASC'): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->innerJoin('i.principal', 'p')
            ->innerJoin('i.customer', 'c')
            ->where('p IN (:allowedPrincipals)')
            ->setParameter('allowedPrincipals', $allowedPrincipals);

        if($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('i.id', ':queryExact'),
                    $qb->expr()->like('i.number', ':queryLike'),
                    $qb->expr()->like('i.hCustomerName', ':queryLike'),
                    $qb->expr()->like('i.hCustomerShortName', ':queryLike'),
                    $qb->expr()->eq('c.ledgerAccountNumber', ':queryExact'),
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
                            $qb->expr()->isNull('i.paid'),
                            $qb->expr()->eq('i.paid', '0')
                        )
                    );
                }
            }
        }

        if($sort)
            $qb->orderBy('i.'.$sort, $sortDirection);

        return $qb;
    }

    public function findUnpaidGroupedByCustomers(?string $query, Collection $allowedPrincipals, array $queryParameters): array
    {
        $qb = $this->findBySearch($query, $allowedPrincipals, $queryParameters);

        $customers = [];
        /** @var Invoice $invoice */
        foreach($qb->getQuery()->getResult() as $invoice) {
            $unique = 'CUSTOMER'.$invoice->getCustomer()->getId().'#'.'CURRENCY'.$invoice->getCurrency()->getId();

            $amountDue = ($invoice->isInvoice() ? $invoice->getAmountDue() : $invoice->getAmountDue()*-1);

            if(isset($customers[$unique])) {
                $customers[$unique]['sumAmountDue'] += $amountDue;
                $customers[$unique]['invoices'][] = $invoice;

            } else {
                $customers[$unique] = [
                    'customer' => $invoice->getCustomer(),
                    'currency' => $invoice->getCurrency(),
                    'sumAmountDue' => $amountDue,
                    'invoices' => [$invoice],
                ];
            }
        }

        return $customers;
    }

    public function getNextAvailableDocumentNumber(Principal $principal): ?int
    {
        $fibuDocumentNumberRange = $principal->getFibuDocumentNumberRange();

        if(!$fibuDocumentNumberRange) {
            $this->logger->warning('InvoiceRepository->getNextAvailableDocumentNumber: Kein $fibuDocumentNumberRange für Mandant #{id} definiert (NULL)', ['id' => $principal->getId()]);
            return null;
        }

        $qb = $this->createQueryBuilder('invoice');
        $qb->select('MAX(invoice.number) AS max')
            ->join('invoice.principal', 'principal')
            ->where($qb->expr()->eq('principal.id', ':principal'))
            ->andWhere($qb->expr()->like('invoice.number', ':fibuDocumentNumberRange'))
            ->setParameter('fibuDocumentNumberRange', $fibuDocumentNumberRange.'%')
            ->setParameter('principal', $principal);
        try {
            $result = $qb->getQuery()->getSingleResult();
            $this->logger->debug('InvoiceRepository->getNextAvailableDocumentNumber: Relevanter Eintrag zu {fibuDocumentNumberRange} für Mandant #{id} gefunden, MAX: {max}', ['fibuDocumentNumberRange' => $fibuDocumentNumberRange, 'id' => $principal->getId(), 'max' => $result['max']] );
        } catch (NoResultException|NonUniqueResultException) {
            $this->logger->info('InvoiceRepository->getNextAvailableDocumentNumber: Kein relevanter Eintrag zu {fibuDocumentNumberRange} für Mandant #{id} gefunden', ['fibuDocumentNumberRange' => $fibuDocumentNumberRange, 'id' => $principal->getId()] );
            return null;
        }
        if($result['max'] == null)
            return $fibuDocumentNumberRange.'0001';
        else
            return $result['max']+1;
    }

}
