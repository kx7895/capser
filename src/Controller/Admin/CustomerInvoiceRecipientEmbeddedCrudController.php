<?php

namespace App\Controller\Admin;

use App\Entity\CustomerInvoiceRecipient;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerInvoiceRecipientEmbeddedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerInvoiceRecipient::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),

            TextField::new('name', 'Name')
                ->setColumns(6),
            TextField::new('email', 'E-Mail')
                ->setColumns(6)
        ];
    }
}
