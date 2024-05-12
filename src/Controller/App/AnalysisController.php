<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PrincipalRepository;
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
        private readonly PrincipalRepository         $principalRepository,
        private readonly DataTableService            $dataTableService,
    ) {}

    #[Route('/liquidity/unpaid/customers', name: 'liquidity_unpaid_customers', methods: ['GET'])]
    public function liquidityUnpaidCustomers(
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $itemsPerPage = 50000,
        #[MapQueryParameter] string $sort = 'due',
        #[MapQueryParameter] string $sortDirection = 'ASC',
        #[MapQueryParameter] string $query = null,
        #[MapQueryParameter] string $queryPrincipalId = null,
        #[MapQueryParameter] string $queryCustomerId = null,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $allowedPrincipals = $user->getPrincipals();
        $allowedCustomers = $this->customerRepository->findAllowed($allowedPrincipals);

        $sort = $this->dataTableService->validateSort($sort, ['date', 'invoiceType', 'number', 'hCustomerName', 'hPrincipalShortName', 'periodFrom', 'amountGross', 'due']);
        $sortDirection = $this->dataTableService->validateSortDirection($sortDirection);

        $queryPrincipal = null;
        if((int)$queryPrincipalId) {
            $queryPrincipal = $this->principalRepository->find($queryPrincipalId);
            if(!$queryPrincipal)
                return throw $this->createNotFoundException();
            $queryPrincipal = $this->dataTableService->validatePrincipalSelect($queryPrincipal, $allowedPrincipals);
        }

        $queryCustomer = null;
        if((int)$queryCustomerId) {
            $queryCustomer = $this->customerRepository->find($queryCustomerId);
            if(!$queryCustomer)
                return throw $this->createNotFoundException();
            $queryPrincipal = $this->dataTableService->validateCustomerSelect($queryCustomer, $allowedPrincipals);
        }

        $queryParameters = [
            'draft' => false,
            'paid' => false
        ];
        if($queryPrincipal)
            $queryParameters['principal'] = $queryPrincipal;
        if($queryCustomer)
            $queryParameters['customer'] = $queryCustomer;

        $invoices = $this->dataTableService->buildDataTable($this->invoiceRepository, $allowedPrincipals, $query, $queryParameters, $sort, $sortDirection, $page, $itemsPerPage);

        return $this->render('app/analysis/liquidity_unpaid_customers.html.twig', [
            'invoices' => $invoices,

            'allowedPrincipals' => $allowedPrincipals,
            'queryPrincipal' => $queryPrincipal,
            'allowedCustomers' => $allowedCustomers,
            'queryCustomer' => $queryCustomer,
            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'query' => $query,
        ]);
    }

}