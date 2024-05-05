<?php

namespace App\Controller\App;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoiceAttachment;
use App\Entity\User;
use App\Form\InvoiceAttachmentFormType;
use App\Form\InvoiceFormType;
use App\Form\InvoicePaymentFormType;
use App\Form\InvoicePositionsFormType;
use App\Repository\CurrencyRepository;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceAttachmentRepository;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceTypeRepository;
use App\Repository\PrincipalRepository;
use App\Service\DataTableService;
use App\Service\InvoiceCreatePdfService;
use App\Service\InvoiceCreateService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/app/invoice', name: 'app_invoice_')]
class InvoiceController extends AbstractController
{
    public function __construct(
        private readonly CustomerRepository          $customerRepository,
        private readonly CurrencyRepository          $currencyRepository,
        private readonly InvoiceRepository           $invoiceRepository,
        private readonly InvoiceTypeRepository       $invoiceTypeRepository,
        private readonly InvoiceAttachmentRepository $invoiceAttachmentRepository,
        private readonly PrincipalRepository         $principalRepository,
        private readonly DataTableService            $dataTableService,
        private readonly InvoiceCreateService        $invoiceCreateService,
        private readonly EntityManagerInterface      $entityManager,
        private readonly LoggerInterface             $logger,
        private readonly SluggerInterface            $slugger,
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $itemsPerPage = 20, // TODO: Vielleicht in Benutzer-Einstellungen setzen lassen.
        #[MapQueryParameter] string $sort = 'date',
        #[MapQueryParameter] string $sortDirection = 'DESC',
        #[MapQueryParameter] string $query = null,
        #[MapQueryParameter] string $queryPrincipalId = null,
        #[MapQueryParameter] string $queryCustomerId = null,
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $allowedPrincipals = $user->getPrincipals();
        $allowedCustomers = $this->customerRepository->findAllowed($allowedPrincipals);

        $sort = $this->dataTableService->validateSort($sort, ['date', 'invoiceType', 'hCustomerName', 'hPrincipalName', 'number', 'amountNet', 'createdAt']);
        $sortDirection = $this->dataTableService->validateSortDirection($sortDirection);

        $queryPrincipal = null;
        if((int)$queryPrincipalId) {
            $queryPrincipal = $this->principalRepository->find($queryPrincipalId);
            if(!$queryPrincipal)
                return throw $this->createNotFoundException();
            $queryPrincipal = $this->dataTableService->validatePrincipalSelect($queryPrincipal, $allowedPrincipals); // TODO: Security - vielleicht als Voters?
        }

        $queryCustomer = null;
        if((int)$queryCustomerId) {
            $queryCustomer = $this->customerRepository->find($queryCustomerId);
            if(!$queryCustomer)
                return throw $this->createNotFoundException();
            $queryPrincipal = $this->dataTableService->validateCustomerSelect($queryCustomer, $allowedPrincipals); // TODO: Security - vielleicht als Voters?
        }

        $queryParameters = [];
        if($queryPrincipal)
            $queryParameters['principal'] = $queryPrincipal;
        if($queryCustomer)
            $queryParameters['customer'] = $queryCustomer;

        // TODO: Security - nur Invoices für Customers von eigenen Principals! Voters!
        $invoices = $this->dataTableService->buildDataTable($this->invoiceRepository, $query, $queryParameters, $sort, $sortDirection, $page, $itemsPerPage);

        return $this->render('app/invoice/index.html.twig', [
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

    #[Route('/new/basics', name: 'new_basics', methods: ['GET', 'POST'])]
    public function newBasics(Request $request): Response
    {
        $invoice = $this->findOrCreateInvoice($request, true);
        if(!$invoice)
            return $this->redirectToRoute('app_invoice_index');

        $form = $this->createInvoiceForm($request, $invoice, 1);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // define due date
            $due = $invoice->getDate();
            if($invoice->getTermOfPayment() && $invoice->getTermOfPayment()->getDueDays() > 0)
                $due = $due->modify('+'.$invoice->getTermOfPayment()->getDueDays().' days');
            $invoice->setDue($due);

            // define historic/cache fields
            $invoice->setHCustomerName($invoice->getCustomer()->getName());
            $invoice->setHCustomerShortName($invoice->getCustomer()->getShortName());
            $invoice->setHPrincipalName($invoice->getPrincipal()->getName());
            $invoice->setHPrincipalShortName($invoice->getPrincipal()->getShortName());

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_invoice_new_positions', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
        }

        return $this->render('app/invoice/new_basics.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/new/positions', name: 'new_positions', methods: ['GET', 'POST'])]
    public function newPositions(Request $request): Response
    {
        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request, false);
        if(!$invoice)
            return $this->redirectToRoute('app_invoice_index');

        $form = $this->createInvoiceForm($request, $invoice, 2);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            $redirectTarget = 'new_positions';
            if($form->get('finalize')->isClicked() || $form->get('finalizeXXL')->isClicked())
                $redirectTarget = 'new_final';
            elseif($form->get('return')->isClicked() || $form->get('returnXXL')->isClicked())
                $redirectTarget = 'new_basics';

            if($redirectTarget == 'new_positions') {
                $this->addFlash('success', ['Entwurf', 'Der aktuelle Entwurf des Beleges wurde erfolgreich gespeichert.']);
            }

            return $this->redirectToRoute('app_invoice_'.$redirectTarget, ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
        }

        return $this->render('app/invoice/new_positions.html.twig', [
            'paymentSentence' => $this->invoiceCreateService->getPaymentSentence($invoice),
            'taxSentence' => $this->invoiceCreateService->getVatSentence($invoice),
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/new/preview', name: 'new_preview')]
    public function newPreview(Request $request): Response
    {
        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request, false);
        if(!$invoice)
            return $this->redirectToRoute('app_invoice_index');

        [, $pdf] = $this->buildInvoice($invoice);

        return new Response($pdf->Output('I', $invoice->getNiceFilename()), 200, array('Content-Type' => 'application/pdf'));
    }

    #[Route('/new/final', name: 'new_final')]
    public function newFinal(Request $request): Response
    {
        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request, false);
        if(!$invoice)
            return $this->redirectToRoute('app_invoice_index');

        [$invoice, $pdf] = $this->buildInvoice($invoice, true);

        $pdf->Output('F', $this->invoiceCreateService->buildFullPathToFile($invoice->getStorageFilename()));
        unset($pdf);

        $this->invoiceCreateService->sendToFibu($invoice);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Beleg wurde erfolgreich erstellt und kann nun per E-Mail verschickt werden.']);
        return $this->redirectToRoute('app_invoice_index', $this->dataTableService->parametersFromQueryToArray($request));
    }

    private function buildInvoice(Invoice $invoice, $isFinal = false): array
    {
        if($isFinal)
            $invoice->setNumber($this->invoiceCreateService->buildInvoiceNumber((int)$invoice->getPrincipal()->getFibuDocumentNumberRange()));
        else
            $invoice->setNumber(99999999);
        $invoice->setStorageFilename($this->invoiceCreateService->buildInvoiceStorageFilename($invoice));
        $invoice->setNiceFilename($this->invoiceCreateService->buildInvoiceNiceFilename($invoice));

        // PDF CREATION
        $pdfCreator = new InvoiceCreatePdfService($invoice);

        $pdfCreator->addPdfHeaderAddress();
        $pdfCreator->addPdfHeaderInvoiceOverview();

        $pdfCreator->addPdfTableHeadRow();

        $totalSum = 0;
        foreach($invoice->getInvoicePositions() as $position) {
            $pdfCreator->addPdfTableBodyRow([
                $position->getText(),
                number_format($position->getAmount(), 2, ',', '.'),
                number_format($position->getPrice(), 2, ',', '.'),
                number_format($position->getPrice() * $position->getAmount(), 2, ',', '.'),
                number_format($invoice->getVatRate(), 2, ',', '.')
            ]);
            $totalSum += $position->getPrice() * $position->getAmount();
        }

        $invoice->setAmountNet(round($totalSum, 2));
        $invoice->setAmountGross(round(($invoice->getAmountNet() * $invoice->getVatRate() / 100), 2) + $invoice->getAmountNet());
        $pdfCreator->addPdfTableSumRows($invoice->getAmountNet(), $invoice->getVatRate(), $invoice->getAmountGross());

        $pdfCreator->addPdfConditions([$this->invoiceCreateService->getVatSentence($invoice), $this->invoiceCreateService->getPaymentSentence($invoice), $invoice->getOutroText()]);
        $pdfCreator->addPdfFooter();

        $pdf = $pdfCreator->getPdf();

        return[$invoice, $pdf];
    }

    #[Route('/{id}', name: 'show', methods: ['GET', 'POST'])]
    public function show(Invoice $invoice, Request $request): Response
    {
        // TODO: Security - nur Invoices für eigene Principals! Voters!

        $paymentForm = null;
//        $uploadAttachmentForm = null;

        $parameters = $this->dataTableService->parametersFromQueryToArray($request);
        if($invoice->getId())
            $parameters['id'] = $invoice->getId();

        if($invoice->getPaymentStatus() != 'paid') {
            // Build Form
            $paymentForm = $this->createForm(InvoicePaymentFormType::class, $invoice, [
                'action' => $this->generateUrl('app_invoice_show', $parameters),
            ]);

            // Set Default
            $paymentForm->get('paymentDate')->setData(new DateTime());
            $paymentForm->get('paymentAmount')->setData($invoice->getAmountGross());
            $paymentForm->get('paymentCurrency')->setData($invoice->getCurrency());
        }

        $newInvoiceAttachment = new InvoiceAttachment();
        $newInvoiceAttachment->setInvoice($invoice);
        $uploadAttachmentForm = $this->createForm(InvoiceAttachmentFormType::class, $newInvoiceAttachment, [
            'action' => $this->generateUrl('app_invoice_show', $parameters),
        ]);

        if($paymentForm) {
            $paymentForm->handleRequest($request);
            if($paymentForm->isSubmitted() && $paymentForm->isValid()) {
                if($invoice->getPaymentDate()) {
                    $invoice->setPaymentIsPaid(true);
                    $invoice->setPaymentMarkedAt(new DateTimeImmutable()); // TODO: Mit Doctrine Extensions Timestampable arbeiten
                    $invoice->setPaymentMarkedBy($this->getUser()); // TODO: Mit Doctrine Extensions Blamable arbeiten
                }

                $this->entityManager->persist($invoice);
                $this->entityManager->flush();

                $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Zahlungseingang wurde erfolgreich gespeichert.']);
                return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
            }
        }

        $uploadAttachmentForm->handleRequest($request);

        if($uploadAttachmentForm->isSubmitted() && $uploadAttachmentForm->isValid()) {
            $file = $uploadAttachmentForm->get('upload')->getData();
            if($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newStorageFilename = $invoice->getInvoiceType()->getType().$invoice->getNumber().'-'.$safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                $newFilename = $invoice->getInvoiceType()->getType().$invoice->getNumber().'-'.$safeFilename.'.'.$file->guessExtension();

                try {
                    $file->move(
                        'files/invoices/',
                        $newStorageFilename
                    );
                } catch(FileException $e) {
                    $this->logger->warning('Dateiupload (Rechnungsanhang) fehlgeschlagen: '.$e->getMessage().' ('.$e->getCode().')');

                    $this->addFlash('danger', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der neue Anhang konnte nicht hinzugefügt werden, es ist ein Fehler aufgetreten.']);
                    return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
                }

                $newInvoiceAttachment->setStorageFilename('files/invoices/'.$newStorageFilename);
                $newInvoiceAttachment->setNiceFilename($newFilename);
                $newInvoiceAttachment->setCreatedAt(new DateTimeImmutable()); // TODO: Mit Doctrine Extensions Timestampable arbeiten
                $newInvoiceAttachment->setCreatedBy($this->getUser()); // TODO: Mit Doctrine Extensions Blamable arbeiten

                $this->entityManager->persist($newInvoiceAttachment);
                $this->entityManager->flush();
            }

            $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der neue Anhang '.$newInvoiceAttachment->getNiceFilename().' wurde erfolgreich hinzugefügt.']);
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
        } elseif($uploadAttachmentForm->isSubmitted() && !$uploadAttachmentForm->isValid()) {
            $this->addFlash('danger', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Anhang konnte nicht hinzugefügt werden, bitte versuchen Sie es erneut oder verwenden Sie ein anderes Dateiformat.']);
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
        }

        return $this->render('app/invoice/show.html.twig', [
            'paymentForm' => $paymentForm,
            'uploadAttachmentForm' => $uploadAttachmentForm,
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/mail', name: 'mail', methods: ['GET'])]
    public function mailInvoice(Invoice $invoice, Request $request): Response
    {
        // TODO: Security - nur Invoices für eigene Principals! Voters!
        return $this->invoiceActionHelper($invoice, $request, 'mail');
    }

    #[Route('/{id}/remind', name: 'remind', methods: ['GET'])]
    public function remindInvoice(Invoice $invoice, Request $request): Response
    {
        // TODO: Security - nur Invoices für eigene Principals! Voters!
        return $this->invoiceActionHelper($invoice, $request, 'remind');
    }

    private function invoiceActionHelper(Invoice $invoice, Request $request, string $action): RedirectResponse
    {
        if($action == 'mail') {
            if($this->invoiceCreateService->sendToCustomer($invoice))
                $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Beleg wurde erfolgreich wurde erfolgreich per E-Mail verschickt.']);
            else
                $this->addFlash('danger', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Beleg konnte nicht per E-Mail verschickt werden, es ist ein Fehler aufgetreten.']);
        } elseif($action == 'remind') {
            if($this->invoiceCreateService->sendToCustomer($invoice, true))
                $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Die Zahlungserinnerung wurde erfolgreich wurde erfolgreich per E-Mail verschickt.']);
            else
                $this->addFlash('danger', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Die Zahlungserinnerung konnte nicht per E-Mail verschickt werden, es ist ein Fehler aufgetreten.']);
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
    }

    #[Route('/{id}/attachment/delete', name: 'attachment_delete', methods: ['GET'])]
    public function deleteAttachmentInvoice(Invoice $invoice, Request $request): Response
    {
        // TODO: Security - nur Customers für eigene Principals! Voters!

        if($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->get('_token'))) {
            $idInvoiceAttachment = $request->query->get('idInvoiceAttachment');
            $invoiceAttachment = $this->invoiceAttachmentRepository->find($idInvoiceAttachment);
            $niceFilename = $invoiceAttachment->getNiceFilename();
            unlink($invoiceAttachment->getStorageFilename());
            $this->entityManager->remove($invoiceAttachment);
            $this->entityManager->flush();

            $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Anhang '.$niceFilename.' wurde erfolgreich entfernt.']);
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId(), ...$this->dataTableService->parametersFromQueryToArray($request)]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    public function delete(Invoice $invoice, Request $request): Response
    {
        // TODO: Security - nur Customers für eigene Principals! Voters!

        if($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->get('_token'))) {
            if($invoice->getNumber())
                $name = $invoice->getInvoiceType()->getType().' '.$invoice->getNumber();
            else
                $name = $invoice->getInvoiceType()->getName().' für '.$invoice->getCustomerName().', Entwurf';

            $this->entityManager->remove($invoice);
            $this->entityManager->flush();

            $this->addFlash('success', [$name, 'Der Beleg wurde erfolgreich gelöscht.']);
        }

        return $this->redirectToRoute('app_invoice_index', $this->dataTableService->parametersFromQueryToArray($request));
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['GET'])]
    public function cancel(Invoice $invoice, Request $request): Response
    {
        return $this->render('app/layout/betatest.html.twig');
    }

    #[Route('/{id}/copy', name: 'copy', methods: ['GET'])]
    public function copy(Invoice $invoice, Request $request): Response
    {
        return $this->render('app/layout/betatest.html.twig');
    }

    private function createInvoiceForm(Request $request, Invoice $invoice, int $step = null): FormInterface
    {
        $parameters = $this->dataTableService->parametersFromQueryToArray($request);
        if($invoice->getId())
            $parameters['id'] = $invoice->getId();

        if($step == 1)
            return $this->createForm(InvoiceFormType::class, $invoice, [
                'action' => $this->generateUrl('app_invoice_new_basics', $parameters),
            ]);
        elseif($step == 2)
            return $this->createForm(InvoicePositionsFormType::class, $invoice, [
                'action' => $this->generateUrl('app_invoice_new_positions', $parameters),
            ]);
        else
            throw new LogicException('Invalid Form Step for InvoiceForm.');

    }

    /**
     * HELPER für Bearbeitungsmasken und finale Masken von initial angelegten Rechnungen. Gibt die Rechnung zurück oder null im Fehlerfall.
     */
    private function findOrCreateInvoice(Request $request, bool $createWhenMissing = false): ?Invoice
    {
        /** @var User $user */
        $user = $this->getUser();
        $allowedPrincipals = $user->getPrincipals();

        $invoice = null;
        if($request->query->has('id')) {
            // TODO: Security - Nur wenn für Rechnung berechtigt.
            $invoice = $this->invoiceRepository->find($request->query->get('id'));
        }

        if($invoice == null) {
            if($createWhenMissing) {
                $invoice = new Invoice();
                $invoice->setCreatedAt(new DateTimeImmutable()); // TODO: Mit Doctrine Extensions Timestampable arbeiten
                $invoice->setCreatedBy($this->getUser()); // TODO: Mit Doctrine Extensions Blamable arbeiten
                if(count($allowedPrincipals) === 1)
                    $invoice->setPrincipal($allowedPrincipals[0]);
                $invoice->setDate(new DateTimeImmutable());
                $invoice->setPeriodFrom((new DateTimeImmutable())->modify('first day of this month'));
                $invoice->setPeriodTo((new DateTimeImmutable())->modify('last day of this month'));
                $invoice->setLanguage($user->getLanguage()); // TODO: Abhängig vom Kunden, nicht vom Benutzer
                $invoice->setCurrency($this->currencyRepository->findOneBy(['alpha3' => 'EUR'])); // TODO: Abhängig vom Kunden, nicht Standard
                $invoice->setVatType('RC'); // TODO: Abhängig vom Kunden, nicht Standard
                $invoice->setVatRate(0); // TODO: Abhängig vom Kunden, nicht Standard
                $invoice->setInvoiceType($this->invoiceTypeRepository->findOneBy(['type' => 'IN']));
                return $invoice;
            }

            $this->addFlash('warning', ['Fehler', 'Der angeforderte Beleg kann nicht aufgerufen werden [IC:IAP1].']);
            return null;
        }

        if($invoice->getNumber() != null) {
            $this->addFlash('warning', ['Fehler', 'Bereits finalisierte Belege können nicht bearbeitet werden [ICC:IAP2]']);
            return null;
        }

        return $invoice;
    }

}