<?php

namespace App\Controller\App;

use App\Entity\Invoice;
use App\Entity\InvoiceAttachment;
use App\Entity\InvoicePayment;
use App\Entity\InvoicePosition;
use App\Entity\User;
use App\Form\InvoiceAttachmentFormType;
use App\Form\InvoiceFormType;
use App\Form\InvoicePaymentFormType;
use App\Form\InvoicePositionsFormType;
use App\Repository\CurrencyRepository;
use App\Repository\CustomerRepository;
use App\Repository\InvoiceAttachmentRepository;
use App\Repository\InvoicePaymentRepository;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceTypeRepository;
use App\Repository\LanguageRepository;
use App\Service\DataTableService;
use App\Service\InvoiceCreatePdfService;
use App\Service\InvoiceCreateService;
use App\Service\UserPreferenceService;
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
        private readonly InvoicePaymentRepository    $invoicePaymentRepository,
        private readonly LanguageRepository          $languageRepository,
        private readonly DataTableService            $dataTableService,
        private readonly InvoiceCreateService        $invoiceCreateService,
        private readonly UserPreferenceService       $prefs,
        private readonly EntityManagerInterface      $entityManager,
        private readonly LoggerInterface             $logger,
        private readonly SluggerInterface            $slugger,
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        #[MapQueryParameter] int $page = 1,
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
        $this->logger->debug('InvoiceController->index(): {user}', ['user' => $user->getUserIdentifier()]);
        $allowedPrincipals = $user->getPrincipals();
        $allowedCustomers = $this->customerRepository->findAllowed($allowedPrincipals);

        // FILTER
        if($request->query->has('clear') && $request->query->get('clear')) {
            $this->prefs->set($user, 'InvoiceController_index_queryPrincipalId', null);
            $this->prefs->set($user, 'InvoiceController_index_queryCustomerId', null);
        }
        $queryPrincipalId = $this->prefs->handle($user, 'InvoiceController_index_queryPrincipalId', $queryPrincipalId);
        $queryPrincipal = $this->dataTableService->processPrincipalSelect($queryPrincipalId, $allowedPrincipals);
        $queryCustomerId = $this->prefs->handle($user, 'InvoiceController_index_queryCustomerId', $queryCustomerId);
        $queryCustomer = $this->dataTableService->processCustomerSelect($queryCustomerId, $allowedPrincipals);
        $activeFilters = 0;
        if($queryPrincipal) $activeFilters++;
        if($queryCustomer) $activeFilters++;

        // SEARCH
        $query = $this->prefs->handle($user, 'InvoiceController_index_query', $query);

        // PAGINATION
        $itemsPerPage = $this->prefs->get($user, 'itemsPerPage');
        $sort = $this->prefs->handle($user, 'InvoiceController_index_sort', $sort);
        $sort = $this->dataTableService->validateSort($sort, ['date', 'invoiceType', 'hCustomerName', 'hPrincipalName', 'number', 'amountGross', 'createdAt'], 'date');
        $sortDirection = $this->prefs->handle($user, 'InvoiceController_index_sortDirection', $sortDirection);
        $sortDirection = $this->dataTableService->validateSortDirection($sortDirection, 'DESC');

        // TABLE
        $queryParameters = [];
        if($queryPrincipal)
            $queryParameters['principal'] = $queryPrincipal;
        if($queryCustomer)
            $queryParameters['customer'] = $queryCustomer;
        $invoices = $this->dataTableService->buildDataTable($this->invoiceRepository, $allowedPrincipals, $query, $queryParameters, $sort, $sortDirection, $page, $itemsPerPage);
        if(count($invoices) > 0)
            $this->logger->debug('InvoiceController->index(): Bis zu {count} Zeilen angezeigt', ['user' => $user->getUserIdentifier(), 'count' => count($invoices)]);

        return $this->render('app/invoice/index.html.twig', [
            'allowedPrincipals' => $allowedPrincipals,
            'queryPrincipal' => $queryPrincipal,
            'allowedCustomers' => $allowedCustomers,
            'queryCustomer' => $queryCustomer,
            'query' => $query,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'activeFilters' => $activeFilters,

            'invoices' => $invoices,
        ]);
    }

    #[Route('/new', name: 'new_basics', methods: ['GET', 'POST'])]
    public function newBasics(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request, true);
        if(!$invoice)
            return $this->invoiceLogErrorAndRedirectToIndex('newBasics', 'ICnB1');

        $form = $this->createInvoiceForm($invoice, 1);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('InvoiceController->newBasics(): {user} - Form submitted', ['user' => $user->getUserIdentifier()]);

            // define due date
            $due = clone $invoice->getDate();
            if($invoice->getTermOfPayment() && $invoice->getTermOfPayment()->getDueDays() > 0)
                $due = $due->modify('+'.$invoice->getTermOfPayment()->getDueDays().' days');
            $invoice->setDue($due);

            // define historic/cache fields
            $invoice->setHCustomerName($invoice->getCustomer()->getName());
            $invoice->setHCustomerShortName($invoice->getCustomer()->getShortName());
            $invoice->setHPrincipalName($invoice->getPrincipal()->getName());
            $invoice->setHPrincipalShortName($invoice->getPrincipal()->getShortName());

            // copy positions if reference invoice exists
            if($invoice->getInvoiceReference() && $invoice->getInvoicePositions()->count() == 0) {
                foreach($invoice->getInvoiceReference()->getInvoicePositions() as $referenceInvoicePosition) {
                    $invoicePosition = new InvoicePosition();
                    $invoicePosition->setPosition($referenceInvoicePosition->getPosition());
                    $invoicePosition->setUnit($referenceInvoicePosition->getUnit());
                    $invoicePosition->setText($referenceInvoicePosition->getText());
                    $invoicePosition->setAmount($referenceInvoicePosition->getAmount());
                    $invoicePosition->setPrice($referenceInvoicePosition->getPrice());
                    $invoice->addInvoicePosition($invoicePosition);
                }
            }

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_invoice_new_positions', ['id' => $invoice->getId()]);
        } else {
            $this->logger->debug('InvoiceController->newBasics(): {user}', ['user' => $user->getUserIdentifier()]);
        }

        return $this->render('app/invoice/new_basics.html.twig', [
            'invoice' => $invoice,
            'form' => $form,
        ]);
    }

    #[Route('/new/positions', name: 'new_positions', methods: ['GET', 'POST'])]
    public function newPositions(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request);
        if(!$invoice)
            return $this->invoiceLogErrorAndRedirectToIndex('newPositions', 'ICnP1');

        $form = $this->createInvoiceForm($invoice, 2);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('InvoiceController->newPositions(): {user} - Form submitted', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId()]);

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            $redirectTarget = 'new_positions';
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if($form->get('finalize')->isClicked() || $form->get('finalizeXXL')->isClicked())
                $redirectTarget = 'new_final';
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            elseif($form->get('return')->isClicked() || $form->get('returnXXL')->isClicked())
                $redirectTarget = 'new_basics';

            if($redirectTarget == 'new_positions')
                $this->addFlash('success', ['Entwurf', 'Der aktuelle Entwurf des Beleges wurde erfolgreich gespeichert.']);

            return $this->redirectToRoute('app_invoice_'.$redirectTarget, ['id' => $invoice->getId()]);
        } else {
            $this->logger->info('InvoiceController->newPositions(): {user}', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId()]);
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
        /** @var User $user */
        $user = $this->getUser();

        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request);
        if(!$invoice)
            return $this->invoiceLogErrorAndRedirectToIndex('newPreview', 'ICnPr1');

        [, $pdf] = $this->buildInvoice($invoice);

        $this->logger->info('InvoiceController->newPreview(): Durch Benutzer {user} wurde für Beleg #{id} eine PDF-Vorschau erstellt', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId()]);
        return new Response($pdf->Output('I', $invoice->getNiceFilename()), 200, array('Content-Type' => 'application/pdf'));
    }

    #[Route('/new/final', name: 'new_final')]
    public function newFinal(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Invoice $invoice */
        $invoice = $this->findOrCreateInvoice($request);
        if(!$invoice)
            return $this->invoiceLogErrorAndRedirectToIndex('newFinal', 'ICnF1');

        [$invoice, $pdf] = $this->buildInvoice($invoice, true);

        $pdf->Output('F', $this->invoiceCreateService->buildFullPathToFile($invoice->getStorageFilename()));
        unset($pdf);

        $this->invoiceCreateService->sendToFibu($invoice);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        $this->logger->info('InvoiceController->newFinal(): Durch Benutzer {user} wurde der Beleg #{id} ({invoiceType} {invoiceNumber} final erstellt', ['user' => $user->getUserIdentifier(), 'invoiceType' => $invoice->getInvoiceType()->getType(), 'invoiceNumber' => $invoice->getNumber(), 'id' => $invoice->getId()]);
        $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Beleg wurde erfolgreich erstellt und kann nun per E-Mail verschickt werden.']);
        return $this->redirectToRoute('app_invoice_index');
    }

    private function buildInvoice(Invoice $invoice, $isFinal = false): array
    {
        $invoice->setNumber(99999999);
//        if($isFinal)
//            $invoice->setNumber($this->invoiceCreateService->buildInvoiceNumber($invoice->getPrincipal()));
//        else
//            $invoice->setNumber(99999999);
        $invoice->setStorageFilename($this->invoiceCreateService->buildInvoiceStorageFilename($invoice));
        $invoice->setNiceFilename($this->invoiceCreateService->buildInvoiceNiceFilename($invoice));

        // PDF CREATION
        $pdfCreator = new InvoiceCreatePdfService($invoice);

        $pdfCreator->addPdfHeaderAddress();
        $pdfCreator->addPdfHeaderInvoiceOverview();

        $pdfCreator->addPdfTableHeadRow();

        $totalSum = 0;
        foreach($invoice->getInvoicePositions() as $position) {
            $unit = '';
            if($position->getUnit()) {
                $method = 'getName'.ucfirst(strtolower($invoice->getLanguage()->getAlpha2()));
                if($method == 'getNameDe' || $method == 'getNameCh')
                    $method = 'getName';
                $unit = ($position->getUnit()->$method() ? $position->getUnit()->$method() : $position->getUnit()->getName());
            }

            $pdfCreator->addPdfTableBodyRow([
                $position->getText(),
                number_format($position->getAmount(), 2, ',', '.'),
                $unit,
                number_format($position->getPrice(), 2, ',', '.'),
                number_format($position->getPrice() * $position->getAmount(), 2, ',', '.')
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
        /** @var User $user */
        $user = $this->getUser();
        $this->logger->info('InvoiceController->show(): {user}', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId()]);

        if(!$this->isAllowedForInvoice($user, $invoice, 'show'))
            return $this->redirectToRoute('app_invoice_index');

        $paymentForm = $invoicePayment = null;
        if(!$invoice->isPaid()) {
            $invoicePayment = new InvoicePayment();
            $invoicePayment->setInvoice($invoice);

            $paymentForm = $this->createForm(InvoicePaymentFormType::class, $invoicePayment, [
                'action' => $this->generateUrl('app_invoice_show', ['id' => $invoice->getId()]),
            ]);

            // Set Default
            $paymentForm->get('date')->setData(new DateTime());
            if(!$invoice->getInvoicePayments()->isEmpty()) {
                $reduce = 0;
                foreach($invoice->getInvoicePayments() as $existingInvoicePayment)
                    $reduce += $existingInvoicePayment->getAmount();
                $paymentForm->get('amount')->setData($invoice->getAmountGross()-$reduce);
            } else {
                $paymentForm->get('amount')->setData($invoice->getAmountGross());
            }
            $paymentForm->get('currency')->setData($invoice->getCurrency());
        }

        $newInvoiceAttachment = new InvoiceAttachment();
        $newInvoiceAttachment->setInvoice($invoice);
        $uploadAttachmentForm = $this->createForm(InvoiceAttachmentFormType::class, $newInvoiceAttachment, [
            'action' => $this->generateUrl('app_invoice_show', ['id' => $invoice->getId()]),
        ]);

        if($paymentForm && $invoicePayment) {
            $paymentForm->handleRequest($request);
            if($paymentForm->isSubmitted() && $paymentForm->isValid()) {
                $invoicePayment->setCreatedAt(new DateTimeImmutable());
                $invoicePayment->setCreatedBy($user);

                if($invoicePayment->getCurrency() === $invoice->getCurrency()) {
                    $sum = $invoicePayment->getAmount();
                    foreach($invoice->getInvoicePayments() as $existingInvoicePayment)
                        $sum += $existingInvoicePayment->getAmount();
                    if($sum == $invoice->getAmountGross()) {
                        $invoice->setPaid(true);
                    }
                }

                $invoice->addInvoicePayment($invoicePayment);

                $this->entityManager->persist($invoice);
                $this->entityManager->flush();

                $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Zahlungseingang wurde erfolgreich gespeichert.']);
                return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
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
                    return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
                }

                $newInvoiceAttachment->setStorageFilename('files/invoices/'.$newStorageFilename);
                $newInvoiceAttachment->setNiceFilename($newFilename);
                $newInvoiceAttachment->setCreatedAt(new DateTimeImmutable());
                $newInvoiceAttachment->setCreatedBy($this->getUser());

                $this->entityManager->persist($newInvoiceAttachment);
                $this->entityManager->flush();
            }

            $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der neue Anhang '.$newInvoiceAttachment->getNiceFilename().' wurde erfolgreich hinzugefügt.']);
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        } elseif($uploadAttachmentForm->isSubmitted() && !$uploadAttachmentForm->isValid()) {
            $this->addFlash('danger', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Anhang konnte nicht hinzugefügt werden, bitte versuchen Sie es erneut oder verwenden Sie ein anderes Dateiformat.']);
            return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('app/invoice/show.html.twig', [
            'paymentForm' => $paymentForm,
            'uploadAttachmentForm' => $uploadAttachmentForm,
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/mail', name: 'mail', methods: ['GET'])]
    public function mailInvoice(Invoice $invoice): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'mailInvoice'))
            return $this->redirectToRoute('app_invoice_index');

        return $this->invoiceActionHelper($invoice, 'mail');
    }

    #[Route('/{id}/remind', name: 'remind', methods: ['GET'])]
    public function remindInvoice(Invoice $invoice): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'remindInvoice'))
            return $this->redirectToRoute('app_invoice_index');

        return $this->invoiceActionHelper($invoice, 'remind');
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['GET'])]
    public function cancelInvoice(Invoice $invoice): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'cancelInvoice'))
            return $this->redirectToRoute('app_invoice_index');

        return $this->invoiceActionHelper($invoice, 'cancel');
    }

    #[Route('/{id}/paid', name: 'paid', methods: ['GET'])]
    public function paidInvoice(Invoice $invoice): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'paidInvoice'))
            return $this->redirectToRoute('app_invoice_index');

        return $this->invoiceActionHelper($invoice, 'paid');
    }

    private function invoiceActionHelper(Invoice $invoice, string $action): RedirectResponse
    {
        $success = $successMessage = $failMessage = null;

        if($action == 'mail') {
            $success = $this->invoiceCreateService->sendToCustomer($invoice);
            $successMessage = 'Der Beleg wurde erfolgreich per E-Mail verschickt.';
            $failMessage = 'Der Beleg konnte nicht per E-Mail verschickt werden, es ist ein Fehler aufgetreten.';
        } elseif($action == 'remind') {
            $success = $this->invoiceCreateService->sendToCustomer($invoice, true);
            $successMessage = 'Die Zahlungserinnerung wurde erfolgreich per E-Mail verschickt.';
            $failMessage = 'Die Zahlungserinnerung konnte nicht per E-Mail verschickt werden, es ist ein Fehler aufgetreten.';
        } elseif($action == 'cancel') {
            $success = $this->invoiceCreateService->cancel($invoice);
            $successMessage = 'Die Rechnung wurde erfolgreich storniert.';
            $failMessage = 'Die Rechnung konnte nicht storniert werden, es ist ein Fehler aufgetreten.';
        } elseif($action == 'paid') {
            $invoice->setPaid(true);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $success = true;
            $successMessage = 'Die Rechnung wurde als bezahlt markiert.';
        }

        if($success && $successMessage)
            $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), $successMessage]);
        elseif($failMessage)
            $this->addFlash('danger', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), $failMessage]);

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/attachment/delete', name: 'attachment_delete', methods: ['GET'])]
    public function deleteAttachmentInvoice(Invoice $invoice, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'deleteAttachmentInvoice'))
            return $this->redirectToRoute('app_invoice_index');

        if($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->get('_token'))) {
            $idInvoiceAttachment = $request->query->get('idInvoiceAttachment');
            $invoiceAttachment = $this->invoiceAttachmentRepository->find($idInvoiceAttachment);
            $niceFilename = $invoiceAttachment->getNiceFilename();
            unlink($invoiceAttachment->getStorageFilename());
            $this->entityManager->remove($invoiceAttachment);
            $this->entityManager->flush();

            $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Der Anhang '.$niceFilename.' wurde erfolgreich entfernt.']);
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/payment/delete', name: 'payment_delete', methods: ['GET'])]
    public function deletePaymentInvoice(Invoice $invoice, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'deletePaymentInvoice'))
            return $this->redirectToRoute('app_invoice_index');

        if($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->get('_token'))) {
            $idInvoicePayment = $request->query->get('idInvoicePayment');
            $invoicePayment = $this->invoicePaymentRepository->find($idInvoicePayment);
            $this->entityManager->remove($invoicePayment);

            $invoice->setPaid(false);
            $this->entityManager->persist($invoice);

            $this->entityManager->flush();

            $this->addFlash('success', [$invoice->getInvoiceType()->getType().' '.$invoice->getNumber(), 'Die erfasste Zahlung wurde erfolgreich entfernt.']);
        }

        return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    public function delete(Invoice $invoice, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$this->isAllowedForInvoice($user, $invoice, 'delete'))
            return $this->redirectToRoute('app_invoice_index');

        if($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->get('_token'))) {
            if($invoice->getNumber()) {
                $name = $invoice->getInvoiceType()->getType().' '.$invoice->getNumber();
                if(!$this->isGranted('ROLE_SUPERADMIN')) {
                    $this->addFlash('danger', [$name, 'Finalisierte Belege können nicht storniert werden.']);
                    return $this->redirectToRoute('app_invoice_index');
                }
            } else {
                $name = $invoice->getInvoiceType()->getName().' für '.$invoice->getCustomerName().', Entwurf';
            }

            $copiedInvoices = $this->invoiceRepository->findBy(['invoiceReference' => $invoice]);
            foreach($copiedInvoices as $copiedInvoice) {
                $copiedInvoice->setInvoiceReference(null);
                $this->entityManager->persist($copiedInvoice);
            }

            $this->entityManager->remove($invoice);
            $this->entityManager->flush();

            $this->addFlash('success', [$name, 'Der Beleg wurde erfolgreich gelöscht.']);
        }

        return $this->redirectToRoute('app_invoice_index');
    }

    #[Route('/{id}/copy', name: 'copy', methods: ['GET'])]
    public function copy(Invoice $invoice): Response
    {
        return $this->redirectToRoute('app_invoice_new_basics', ['invoiceReference' => $invoice->getId()]);
    }

    private function createInvoiceForm(Invoice $invoice, int $step = null): FormInterface
    {
        if($step == 1)
            return $this->createForm(InvoiceFormType::class, $invoice);
        elseif($step == 2 && $invoice->getId())
            return $this->createForm(InvoicePositionsFormType::class, $invoice, [
                'action' => $this->generateUrl('app_invoice_new_positions', ['id' => $invoice->getId()]),
                'principal' => $invoice->getPrincipal(),
            ]);
        else
            throw new LogicException('Invalid Form Step for createInvoiceForm.');
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
            $invoice = $this->invoiceRepository->find($request->query->get('id'));
            if(!$this->isAllowedForInvoice($user, $invoice, 'findOrCreateInvoice'))
                return null;
        }

        $invoiceReference = null;
        if($request->query->has('invoiceReference')) {
            $invoiceReference = $this->invoiceRepository->find($request->query->get('invoiceReference'));
            if(!$this->isAllowedForInvoice($user, $invoiceReference, 'findOrCreateInvoice[invoiceReference]'))
                return null;
        }

        if($invoiceReference && $invoice == null && $createWhenMissing) {
            $invoice = new Invoice();
            $invoice->setInvoiceReference($invoiceReference);
            $invoice->setPrincipal($invoiceReference->getPrincipal());
            $invoice->setCustomer($invoiceReference->getCustomer());
            $invoice->setDate(new DateTimeImmutable());
            $invoice->setInvoiceType($invoiceReference->getInvoiceType());
            $invoice->setPeriodFrom($invoiceReference->getPeriodFrom());
            $invoice->setPeriodTo($invoiceReference->getPeriodTo());
            $invoice->setLanguage($invoiceReference->getLanguage());
            $invoice->setIntroText($invoiceReference->getIntroText());
            $invoice->setOutroText($invoiceReference->getOutroText());
            $invoice->setCurrency($invoiceReference->getCurrency());
            $invoice->setAccountingPlanLedger($invoiceReference->getAccountingPlanLedger());
            $invoice->setTermOfPayment($invoiceReference->getTermOfPayment());
            $invoice->setVatType($invoiceReference->getVatType());
            $invoice->setVatRate($invoiceReference->getVatRate());
            $invoice->setCostcenterExternal($invoiceReference->getCostcenterExternal());
            $invoice->setReferenceExternal($invoiceReference->getReferenceExternal());
            $invoice->setCreatedAt(new DateTimeImmutable());
            $invoice->setCreatedBy($this->getUser());
            return $invoice;
        }

        if($invoice == null) {
            if($createWhenMissing) {
                $invoice = new Invoice();
                $invoice->setCreatedAt(new DateTimeImmutable());
                $invoice->setCreatedBy($this->getUser());
                if(count($allowedPrincipals) === 1)
                    $invoice->setPrincipal($allowedPrincipals[0]);
                $invoice->setDate(new DateTimeImmutable());
                $invoice->setPeriodFrom((new DateTimeImmutable())->modify('first day of this month'));
                $invoice->setPeriodTo((new DateTimeImmutable())->modify('last day of this month'));
                $invoice->setInvoiceType($this->invoiceTypeRepository->findOneBy(['type' => 'RE']));
                $invoice->setIntroText($this->prefs->get($user, 'invoiceDefaultIntroText'));
                $invoice->setOutroText($this->prefs->get($user, 'invoiceDefaultOutroText'));
                $invoice->setLanguage($this->languageRepository->find($this->prefs->get($user, 'invoiceDefaultLanguage')));
                $invoice->setCurrency($this->currencyRepository->find($this->prefs->get($user, 'invoiceDefaultCurrency')));
                $invoice->setVatType($this->prefs->get($user, 'invoiceDefaultVatType'));
                $invoice->setVatRate($this->prefs->get($user, 'invoiceDefaultVatRate'));
                return $invoice;
            }

            $this->logger->warning('InvoiceController->findOrCreateInvoice(): Invoice ist null, darf aber auch nicht neu generiert werden.', ['user' => $user->getUserIdentifier()]);
            return null;
        }

        if($invoice->getNumber() != null) {
            $errorCode = time().'-ICfOCI1';
            $this->logger->info('InvoiceController->findOrCreateInvoice(): Bereits finalisierter Beleg #{id} zur Bearbeitung aufgerufen, Error-Code: {eC}', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId(), 'eC' => $errorCode]);
            $this->addFlash('warning', ['Hinweis', 'Bereits finalisierte Belege können nicht bearbeitet werden, Fehler-Code: '.$errorCode]);

            return null;
        }

        return $invoice;
    }

    private function isAllowedForInvoice(User $user, ?Invoice $invoice, string $action): bool
    {
        if(!$invoice) {
            $this->logger->warning('InvoiceController->'.$action.'(): Aufgerufener Beleg zu übergebener ID wurde nicht gefunden, ID unbekannt', ['user' => $user->getUserIdentifier()]);
            return false;
        } elseif(!$invoice->getPrincipal()) {
            $this->logger->warning('InvoiceController->'.$action.'(): Aufgerufener Beleg zu übergebener ID #{id} hat keinen gültigen, validierbaren Principal', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId()]);
            return false;
        } elseif(!$user->getPrincipals()->contains($invoice->getPrincipal())) {
            $this->logger->warning('InvoiceController->'.$action.'(): Aufgerufener Beleg #{id} entspricht keinem berechtigten Mandanten, Beleg wird nicht angezeigt', ['user' => $user->getUserIdentifier(), 'id' => $invoice->getId(), 'principal' => $invoice->getPrincipal()->getId()]);
            return false;
        }

        return true;
    }

    private function invoiceLogErrorAndRedirectToIndex(string $action, string $errorCode): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $errorCode = time().'-'.$errorCode;
        $this->logger->warning('InvoiceController->'.$action.'(): Zugriffs- oder Berechtigungsfehler, Abbruch des Aufrufs, Error-Code: {eC}', ['user' => $user->getUserIdentifier(), 'eC' => $errorCode]);
        $this->addFlash('danger', ['Fehler', 'Die Aktion konnte nicht durchgeführt werden, Fehler-Code: '.$errorCode]);
        return $this->redirectToRoute('app_invoice_index');
    }

}