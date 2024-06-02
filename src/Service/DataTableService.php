<?php

namespace App\Service;

use App\Entity\Customer;
use App\Entity\Principal;
use App\Repository\CustomerRepository;
use App\Repository\PrincipalRepository;
use Doctrine\Common\Collections\Collection;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Log\LoggerInterface;

readonly class DataTableService
{

    public function __construct(
        private CustomerRepository  $customerRepository,
        private PrincipalRepository $principalRepository,
        private LoggerInterface     $logger,
    ) {}

    public function validateSort(?string $selectedSort, array $availableSorts, string $defaultSort = null)
    {
        if(!$selectedSort)
            return $defaultSort;

        return in_array($selectedSort, $availableSorts) ? $selectedSort : ($defaultSort ?: $availableSorts[0]);
    }

    public function processPrincipalSelect(?string $queryPrincipalId, Collection $allowedPrincipals): bool|null|Principal
    {
        $queryPrincipalId = (int)$queryPrincipalId;
        if($queryPrincipalId === 0) {
            return null;
        } else {
            $queryPrincipal = $this->principalRepository->find($queryPrincipalId);
            if(!$queryPrincipal) {
                $this->logger->warning('processPrincipalSelect - PERMISSION EXCEPTION - $queryPrincipalId übergeben, zu der kein gültiger Principal gefunden werden konnte.');
                return false;
            }

            if(!$allowedPrincipals->contains($queryPrincipal)) {
                $this->logger->warning('processPrincipalSelect - PERMISSION EXCEPTION - $queryPrincipal ist nicht Teil von $allowedPrincipals.');
                return false;
            }

            return $queryPrincipal;
        }
    }

    public function processCustomerSelect(?string $queryCustomerId, Collection $allowedPrincipals): bool|null|Customer
    {
        $queryCustomerId = (int)$queryCustomerId;
        if($queryCustomerId === 0) {
            return null;
        } else {
            $queryCustomer = $this->customerRepository->find($queryCustomerId);
            if(!$queryCustomer) {
                $this->logger->warning('processCustomerSelect - PERMISSION EXCEPTION - $queryCustomerId übergeben, zu der kein gültiger Customer gefunden werden konnte.');
                return false;
            }

            if(!$allowedPrincipals->contains($queryCustomer->getPrincipal())) {
                $this->logger->warning('processCustomerSelect - PERMISSION EXCEPTION - $queryCustomerId -> principal ist nicht Teil von $allowedPrincipals.');
                return false;
            }

            return $queryCustomer;
        }
    }

    public function validateSortDirection(?string $selectedSortDirection, string $defaultSortDirection = 'ASC'): string
    {
        if(!$selectedSortDirection)
            return $defaultSortDirection;

        $availableSortOrders = ['ASC', 'DESC'];
        return in_array($selectedSortDirection, $availableSortOrders) ? $selectedSortDirection : $defaultSortDirection;
    }

    public function buildDataTable($repository, Collection $allowedPrincipals, ?string $query, array $queryParameters, string $sort, string $sortDirection, int $page, int $itemsPerPage = 10): Pagerfanta
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($repository->findBySearch($query, $allowedPrincipals, $queryParameters, $sort, $sortDirection), true, true),
            $page,
            $itemsPerPage
        );
    }

}