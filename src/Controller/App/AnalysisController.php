<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use App\Service\DataTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/analysis', name: 'app_analysis_')]
class AnalysisController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository          $customerRepository,
        private readonly InvoiceRepository           $invoiceRepository,
        private readonly DataTableService            $dataTableService,
    ) {}

    #[Route('/liquidity/unpaid/customers', name: 'liquidity_unpaid_customers', methods: ['GET'])]
    public function liquidityUnpaidCustomers(
        #[MapQueryParameter] bool $groupedByCustomers = false,
        #[MapQueryParameter] string $queryPrincipalId = null,
        #[MapQueryParameter] string $queryCustomerId = null,
        #[MapQueryParameter] string $query = null,
        #[MapQueryParameter] string $sort = 'due',
        #[MapQueryParameter] string $sortDirection = 'ASC',
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $allowedPrincipals = $user->getPrincipals();
        $allowedCustomers = $this->customerRepository->findAllowed($allowedPrincipals);

        $queryPrincipal = $this->dataTableService->processPrincipalSelect($queryPrincipalId, $allowedPrincipals);
        $queryCustomer = $this->dataTableService->processCustomerSelect($queryCustomerId, $allowedPrincipals);

        if($queryPrincipal === false || $queryCustomer === false)
            return throw $this->createNotFoundException();

        $queryParameters = [
            'draft' => false,
            'paid' => false
        ];
        if($queryPrincipal)
            $queryParameters['principal'] = $queryPrincipal;
        if($queryCustomer)
            $queryParameters['customer'] = $queryCustomer;

        $urlQueryParts = [
            'groupedByCustomers' => $groupedByCustomers,
            'queryPrincipalId' => $queryPrincipalId,
            'queryCustomerId' => $queryCustomerId,
            'query' => $query,
        ];

        if($groupedByCustomers) {
            $rows = $this->invoiceRepository->findUnpaidGroupedByCustomers($query, $allowedPrincipals, $queryParameters);
            usort($rows, function($a, $b) {
                return strcmp($a['customer']->getName(), $b['customer']->getName());
            });

        } else {
            $sort = $this->dataTableService->validateSort($sort, ['date', 'number', 'hCustomerName', 'hPrincipalShortName', 'periodFrom', 'amountGross', 'due']);
            $sortDirection = $this->dataTableService->validateSortDirection($sortDirection);
            $urlQueryParts['sort'] = $sort;
            $urlQueryParts['sortDirection'] = $sortDirection;

            $rows = $this->dataTableService->buildDataTable($this->invoiceRepository, $allowedPrincipals, $query, $queryParameters, $sort, $sortDirection, 1, 50000);

        }

        return $this->render('app/analysis/liquidity_unpaid_customers.html.twig', [
            'rows' => $rows,

            'allowedPrincipals' => $allowedPrincipals,
            'queryPrincipal' => $queryPrincipal,
            'allowedCustomers' => $allowedCustomers,
            'queryCustomer' => $queryCustomer,
            'query' => $query,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'groupedByCustomers' => $groupedByCustomers,

            'urlQueryParts' => $urlQueryParts,
        ]);

    }

}