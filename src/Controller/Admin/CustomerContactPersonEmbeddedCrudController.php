<?php

namespace App\Controller\Admin;

use App\Entity\CustomerContactPerson;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerContactPersonEmbeddedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerContactPerson::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),

            TextField::new('salutation', 'Anrede')
                ->setColumns(6),
            TextField::new('position', 'Position')
                ->setColumns(6),
            TextField::new('firstName', 'Vorname')
                ->setColumns(6),
            TextField::new('lastName', 'Nachname')
                ->setColumns(6),
            TextField::new('email', 'E-Mail')
                ->setColumns(6),
            TextField::new('phone', 'Telefon')
                ->setColumns(6),
        ];
    }

}
