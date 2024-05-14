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
use Symfony\Component\HttpFoundation\Request;

class DataTableService {

    public function __construct(
        private readonly CustomerRepository  $customerRepository,
        private readonly PrincipalRepository $principalRepository,
        private readonly LoggerInterface     $logger,
    ) {}

    public function validateSort(string $selectedSort, array $availableSorts, string $defaultSort = null)
    {
        return in_array($selectedSort, $availableSorts) ? $selectedSort : ($defaultSort ?: $availableSorts[0]);
    }


    /**
     * @deprecated Wird mittelfristig ersetzt, ab 13.05.2024 ist processPrincipalSelect zu bevorzugen.
     * @todo Wird mittelfristig ersetzt, ab 13.05.2024 ist processPrincipalSelect zu bevorzugen.
     */
    public function validatePrincipalSelect(?Principal $selectedPrincipal, Collection $allowedPrincipals): ?Principal
    {
        if($allowedPrincipals->contains($selectedPrincipal)) {
            return $selectedPrincipal;
        }

        return null;
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

    /**
     * @deprecated Wird mittelfristig ersetzt, ab 13.05.2024 ist processCustomerSelect zu bevorzugen.
     * @todo Wird mittelfristig ersetzt, ab 13.05.2024 ist processCustomerSelect zu bevorzugen.
     */
    public function validateCustomerSelect(?Customer $selectedCustomer, Collection $allowedPrincipals): ?Customer
    {
        if($allowedPrincipals->contains($selectedCustomer->getPrincipal())) {
            return $selectedCustomer;
        }

        return null;
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

    public function validateSortDirection(string $selectedSortDirection, string $defaultSortDirection = 'ASC'): string
    {
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

    public function parametersFromQueryToArray(Request $request): array
    {
        $parameters = [];
        foreach($request->query as $key => $value) {
            if(in_array($key, ['page', 'itemsPerPage', 'sort', 'sortDirection', 'query', 'queryPrincipalId', 'queryCustomerId']) && $value <> '')
                $parameters[$key] = $value;
        }
        return $parameters;
    }

}