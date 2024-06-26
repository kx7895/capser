<?php

namespace App\Twig\Components;

use App\Entity\Invoice;
use App\Form\InvoiceFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
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

    protected function instantiateForm(): FormInterface
    {
        $invoice = $this->initialFormData ?? new Invoice();

        return $this->createForm(InvoiceFormType::class, $invoice, [
            'action' => $invoice->getId() ? $this->generateUrl('app_invoice_new_basics', ['id' => $invoice->getId()]) : $this->generateUrl('app_invoice_new_basics'),
        ]);
    }
}