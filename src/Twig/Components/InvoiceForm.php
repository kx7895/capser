<?php

namespace App\Twig\Components;

use App\Entity\Invoice;
use App\Form\InvoiceFormType;
use App\Service\DataTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class InvoiceForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?Invoice $initialFormData = null;

    public function __construct(
        private readonly DataTableService $dataTableService,
        private readonly RequestStack $requestStack,
    ) {}

    protected function instantiateForm(): FormInterface
    {
        $invoice = $this->initialFormData ?? new Invoice();

        $request = $this->requestStack->getCurrentRequest();

        $parameters = $this->dataTableService->parametersFromQueryToArray($request);
        if($invoice->getId())
            $parameters['id'] = $invoice->getId();

        return $this->createForm(InvoiceFormType::class, $invoice, [
            'action' => $this->generateUrl('app_invoice_new_basics', $parameters),
        ]);
    }
}