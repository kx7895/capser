<?php

namespace App\Controller\Admin;

use App\Entity\InvoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InvoiceTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return InvoiceType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Rechnungstyp')
            ->setEntityLabelInPlural('Rechnungstypen');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('type', 'Type')
                ->setColumns(2),
            TextField::new('name')
                ->setColumns(10),
        ];
    }
}
