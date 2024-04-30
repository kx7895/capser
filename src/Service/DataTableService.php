<?php
namespace App\Service;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class DataTableService {

    public function validateSort(string $selectedSort, array $availableSorts, string $defaultSort = null)
    {
        return in_array($selectedSort, $availableSorts) ? $selectedSort : ($defaultSort ? $defaultSort : $availableSorts[0]);
    }

    public function validateSortDirection(string $selectedSortDirection, string $defaultSortDirection = 'ASC')
    {
        $availableSortOrders = ['ASC', 'DESC'];
        return in_array($selectedSortDirection, $availableSortOrders) ? $selectedSortDirection : $defaultSortDirection;
    }

    public function buildDataTable($repository, ?string $query, string $sort, string $sortDirection, int $page, int $itemsPerPage = 10)
    {
        return Pagerfanta::createForCurrentPageWithMaxPerPage(
            new QueryAdapter($repository->findBySearch($query, $sort, $sortDirection)),
            $page,
            $itemsPerPage
        );
    }

    public function parametersFromQueryToArray(Request $request): array
    {
        $parameters = [];
        foreach($request->query as $key => $value) {
            if(in_array($key, ['page', 'itemsPerPage', 'sort', 'sortDirection', 'query']) && $value <> '')
                $parameters[$key] = $value;
        }
        return $parameters;
    }

}