<?php

namespace App\Controller\App;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\CustomerFormType;
use App\Repository\CustomerRepository;
use App\Service\DataTableService;
use App\Service\UserPreferenceService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/customer', name: 'app_customer_')]
class CustomerController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository     $customerRepository,
        private readonly DataTableService       $dataTableService,
        private readonly UserPreferenceService  $prefs,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface        $logger,
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $sort = null,
        #[MapQueryParameter] string $sortDirection = null,
        #[MapQueryParameter] string $query = null,
        #[MapQueryParameter] string $queryPrincipalId = null,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->logger->debug('CustomerController->index(): {user}', ['user' => $user->getUserIdentifier()]);
        $allowedPrincipals = $user->getPrincipals();

        // FILTER
        if($request->query->has('clear') && $request->query->get('clear')) {
            $this->prefs->set($user, 'CustomerController_index_queryPrincipalId', null);
        }
        $queryPrincipalId = $this->prefs->handle($user, 'CustomerController_index_queryPrincipalId', $queryPrincipalId);
        $queryPrincipal = $this->dataTableService->processPrincipalSelect($queryPrincipalId, $allowedPrincipals);
        $activeFilters = 0;
        if($queryPrincipal) $activeFilters++;

        // SEARCH
        $query = $this->prefs->handle($user, 'CustomerController_index_query', $query);

        // PAGINATION
        $itemsPerPage = $this->prefs->get($user, 'itemsPerPage');
        $sort = $this->prefs->handle($user, 'CustomerController_index_sort', $sort);
        $sort = $this->dataTableService->validateSort($sort, ['name', 'ledgerAccountNumber', 'hPrincipalName', 'createdAt', 'vatId'], 'name');
        $sortDirection = $this->prefs->handle($user, 'CustomerController_index_sortDirection', $sortDirection);
        $sortDirection = $this->dataTableService->validateSortDirection($sortDirection);

        // TABLE
        $queryParameters = [];
        if($queryPrincipal)
            $queryParameters['principal'] = $queryPrincipal;
        $customers = $this->dataTableService->buildDataTable($this->customerRepository, $allowedPrincipals, $query, $queryParameters, $sort, $sortDirection, $page, $itemsPerPage);
        if(count($customers) > 0)
            $this->logger->debug('CustomerController->index(): Bis zu {count} Zeilen angezeigt', ['user' => $user->getUserIdentifier(), 'count' => count($customers)]);

        return $this->render('app/customer/index.html.twig', [
            'allowedPrincipals' => $allowedPrincipals,
            'queryPrincipal' => $queryPrincipal,
            'query' => $query,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'activeFilters' => $activeFilters,

            'customers' => $customers,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $customer = new Customer();

        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('CustomerController->new(): {user} - Form submitted', ['user' => $user->getUserIdentifier()]);

            if($customer->getPrincipal()) {
                $customer->setHPrincipalName($customer->getPrincipal()->getName());
                $customer->setHPrincipalShortName($customer->getPrincipal()->getShortName());
            }

            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            $this->addFlash('success', [$customer->getName(), 'Der Kunde wurde erfolgreich angelegt.']);

            return $this->redirectToRoute('app_customer_index');
        } else {
            $this->logger->debug('CustomerController->new(): {user}', ['user' => $user->getUserIdentifier()]);
        }

        return $this->render('app/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Customer $customer, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->logger->info('CustomerController->edit(): {user}', ['user' => $user->getUserIdentifier(), 'id' => $customer->getId()]);

        if(!$this->isAllowedForCustomer($user, $customer, 'edit'))
            return $this->redirectToRoute('app_customer_index');

        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('CustomerController->edit(): {user} - Form submitted', ['user' => $user->getUserIdentifier(), 'id' => $customer->getId()]);

            if($customer->getPrincipal()) {
                $customer->setHPrincipalName($customer->getPrincipal()->getName());
                $customer->setHPrincipalShortName($customer->getPrincipal()->getShortName());
            }

            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            $this->addFlash('success', [$customer->getName(), 'Der Kunde wurde erfolgreich aktualisiert.']);

            return $this->redirectToRoute('app_customer_edit',  ['id' => $customer->getId()]);
        }

        return $this->render('app/customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    public function delete(Customer $customer, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForCustomer($user, $customer, 'edit'))
            return $this->redirectToRoute('app_customer_index');

        if($this->isCsrfTokenValid('delete'.$customer->getId(), $request->get('_token'))) {
            $name = $customer->getName();
            $this->entityManager->remove($customer);
            $this->entityManager->flush();

            $this->addFlash('success', [$name, 'Der Kunde wurde erfolgreich gelöscht.']);
        }

        return $this->redirectToRoute('app_customer_index');
    }

    private function isAllowedForCustomer(User $user, ?Customer $customer, string $action): bool
    {
        if(!$customer) {
            $this->logger->warning('CustomerController->'.$action.'(): Aufgerufener Kunde zu übergebener ID wurde nicht gefunden, ID unbekannt', ['user' => $user->getUserIdentifier()]);
            return false;
        } elseif(!$customer->getPrincipal()) {
            $this->logger->warning('CustomerController->'.$action.'(): Aufgerufener Kunde zu übergebener ID #{id} hat keinen gültigen, validierbaren Principal', ['user' => $user->getUserIdentifier(), 'id' => $customer->getId()]);
            return false;
        } elseif(!$user->getPrincipals()->contains($customer->getPrincipal())) {
            $this->logger->warning('CustomerController->'.$action.'(): Aufgerufener Kunde #{id} entspricht keinem berechtigten Mandanten, Kunde wird nicht angezeigt', ['user' => $user->getUserIdentifier(), 'id' => $customer->getId(), 'principal' => $customer->getPrincipal()->getId()]);
            return false;
        }

        return true;
    }

}