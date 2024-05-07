<?php
namespace App\Service;

use App\Entity\Customer;
use App\Entity\Principal;
use Doctrine\Common\Collections\Collection;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class DataTableService {

    public function validateSort(string $selectedSort, array $availableSorts, string $defaultSort = null)
    {
        return in_array($selectedSort, $availableSorts) ? $selectedSort : ($defaultSort ?: $availableSorts[0]);
    }

    public function validatePrincipalSelect(?Principal $selectedPrincipal, Collection $allowedPrincipals): ?Principal
    {
        if($allowedPrincipals->contains($selectedPrincipal)) {
            return $selectedPrincipal;
        }

        return null;
    }

    public function validateCustomerSelect(?Customer $selectedCustomer, Collection $allowedPrincipals): ?Customer
    {
        if($allowedPrincipals->contains($selectedCustomer->getPrincipal())) {
            return $selectedCustomer;
        }

        return null;
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
            if(in_array($key, ['page', 'itemsPerPage', 'sort', 'sortDirection', 'query', 'queryPrincipalId']) && $value <> '')
                $parameters[$key] = $value;
        }
        return $parameters;
    }

}