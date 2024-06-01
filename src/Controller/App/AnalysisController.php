<?php

namespace App\Controller\App;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceRepository;
use App\Service\DataTableService;
use App\Service\UserPreferenceService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        private readonly UserPreferenceService       $prefs,
        private readonly LoggerInterface             $logger,
    ) {}

    #[Route('/liquidity/unpaid/customers', name: 'liquidity_unpaid_customers', methods: ['GET'])]
    public function liquidityUnpaidCustomers(
        Request $request,
        #[MapQueryParameter] bool $groupedByCustomers = null,
        #[MapQueryParameter] string $sort = null,
        #[MapQueryParameter] string $sortDirection = null,
        #[MapQueryParameter] string $query = null,
        #[MapQueryParameter] string $queryPrincipalId = null,
        #[MapQueryParameter] string $queryCustomerId = null,
    ): Response
    {
        // USER
        /** @var User $user */
        $user = $this->getUser();
        $this->logger->debug('AnalysisController->index(): {user}', ['user' => $user->getUserIdentifier()]);
        $allowedPrincipals = $user->getPrincipals();
        $allowedCustomers = $this->customerRepository->findAllowed($allowedPrincipals);

        // FILTER
        if($request->query->has('clear') && $request->query->get('clear')) {
            $this->prefs->set($user, 'AnalysisController_index_queryPrincipalId', null);
            $this->prefs->set($user, 'AnalysisController_index_queryCustomerId', null);
            $this->prefs->set($user, 'AnalysisController_index_groupedByCustomers', null);
        }
        $queryPrincipalId = $this->prefs->handle($user, 'AnalysisController_index_queryPrincipalId', $queryPrincipalId);
        $queryPrincipal = $this->dataTableService->processPrincipalSelect($queryPrincipalId, $allowedPrincipals);
        $queryCustomerId = $this->prefs->handle($user, 'AnalysisController_index_queryCustomerId', $queryCustomerId);
        $queryCustomer = $this->dataTableService->processCustomerSelect($queryCustomerId, $allowedPrincipals);
        $groupedByCustomers = $this->prefs->handle($user, 'AnalysisController_index_groupedByCustomers', $groupedByCustomers);
        $activeFilters = 0;
        if($queryPrincipal) $activeFilters++;
        if($queryCustomer) $activeFilters++;
        if($groupedByCustomers) $activeFilters++;

        // SEARCH
        $query = $this->prefs->handle($user, 'AnalysisController_index_query', $query);

        // TABLE
        $queryParameters = [
            'draft' => false,
            'paid' => false
        ];
        if($queryPrincipal)
            $queryParameters['principal'] = $queryPrincipal;
        if($queryCustomer)
            $queryParameters['customer'] = $queryCustomer;

        if($groupedByCustomers) {
            $rows = $this->invoiceRepository->findUnpaidGroupedByCustomers($query, $allowedPrincipals, $queryParameters);
            usort($rows, function($a, $b) {
                return strcmp($a['customer']->getName(), $b['customer']->getName());
            });
        } else {
            // PAGINATION (here: only SORTING)
            $sort = $this->prefs->handle($user, 'AnalysisController_index_sort', $sort);
            $sort = $this->dataTableService->validateSort($sort, ['date', 'number', 'hCustomerName', 'hPrincipalShortName', 'periodFrom', 'amountGross', 'due'], 'due');
            $sortDirection = $this->prefs->handle($user, 'AnalysisController_index_sortDirection', $sortDirection);
            $sortDirection = $this->dataTableService->validateSortDirection($sortDirection);
            $rows = $this->dataTableService->buildDataTable($this->invoiceRepository, $allowedPrincipals, $query, $queryParameters, $sort, $sortDirection, 1, 100000);
        }

        return $this->render('app/analysis/liquidity_unpaid_customers.html.twig', [
            'groupedByCustomers' => $groupedByCustomers,
            'allowedPrincipals' => $allowedPrincipals,
            'queryPrincipal' => $queryPrincipal,
            'allowedCustomers' => $allowedCustomers,
            'queryCustomer' => $queryCustomer,
            'query' => $query,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'activeFilters' => $activeFilters,

            'rows' => $rows,
        ]);

    }

}