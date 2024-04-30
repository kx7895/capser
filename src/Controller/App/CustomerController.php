<?php

namespace App\Controller\App;

use App\Entity\Customer;
use App\Form\CustomerFormType;
use App\Repository\CustomerRepository;
use App\Service\DataTableService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/customer', name: 'app_customer_')]
class CustomerController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository     $customerRepository,
        private readonly DataTableService       $dataTableService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $itemsPerPage = 10, // TODO: Vielleicht in Benutzer-Einstellungen setzen lassen.
        #[MapQueryParameter] string $sort = 'name',
        #[MapQueryParameter] string $sortDirection = 'ASC',
        #[MapQueryParameter] string $query = null,
    ): Response
    {
        $sort = $this->dataTableService->validateSort($sort, ['name', 'ledgerAccountNumber', 'createdAt', 'vatId']);
        $sortDirection = $this->dataTableService->validateSortDirection($sortDirection);

        // TODO: Security - nur Customers für eigene Principals! Voters!
        $customers = $this->dataTableService->buildDataTable($this->customerRepository, $query, $sort, $sortDirection, $page, $itemsPerPage);

        return $this->render('app/customer/index.html.twig', [
            'customers' => $customers,

            'page' => $page,
            'itemsPerPage' => $itemsPerPage,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'query' => $query,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $customer = new Customer();
        $form = $this->createCustomerForm($request, $customer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            $this->addFlash('success', [$customer->getName(), 'Der Kunde wurde erfolgreich angelegt.']);

            return $this->redirectToRoute('app_customer_index', $this->dataTableService->parametersFromQueryToArray($request));
        }

        return $this->render('app/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        // TODO: Security - nur Customers für eigene Principals! Voters!

        return $this->render('app/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer): Response
    {
        // TODO: Security - nur Customers für eigene Principals! Voters!

        $form = $this->createCustomerForm($request, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', [$customer->getName(), 'Der Kunde wurde erfolgreich aktualisiert.']);

            return $this->redirectToRoute('app_customer_edit',  ['id' => $customer->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
        }

        return $this->render('app/customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    public function delete(Request $request, Customer $customer): Response
    {
        // TODO: Security - nur Customers für eigene Principals! Voters!

        if($this->isCsrfTokenValid('delete'.$customer->getId(), $request->get('_token'))) {
            $id = $customer->getId();
            $name = $customer->getName();
            $this->entityManager->remove($customer);
            $this->entityManager->flush();

            $this->addFlash('success', [$name, 'Der Kunde wurde erfolgreich gelöscht.']);
        }

        return $this->redirectToRoute('app_customer_index', $this->dataTableService->parametersFromQueryToArray($request));
    }

    private function createCustomerForm(Request $request, Customer $customer = null): FormInterface
    {
        $customer = $customer ?? new Customer();

        $parameters = $this->dataTableService->parametersFromQueryToArray($request);
        if($customer->getId())
            $parameters['id'] = $customer->getId();

        return $this->createForm(CustomerFormType::class, $customer, [
            'action' => $customer->getId() ? $this->generateUrl('app_customer_edit', $parameters) : $this->generateUrl('app_customer_new', $parameters),
        ]);
    }

}