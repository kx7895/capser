<?php

namespace App\Twig\Components;

use App\Entity\Customer;
use App\Form\CustomerFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class CustomerForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?Customer $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        $customer = $this->initialFormData ?? new Customer();

        return $this->createForm(CustomerFormType::class, $customer, [
            'action' => $customer->getId() ? $this->generateUrl('app_customer_edit', ['id' => $customer->getId()]) : $this->generateUrl('app_customer_new'),
        ]);
    }
}