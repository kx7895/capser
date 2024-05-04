<?php

namespace App\Controller\Admin;

use App\Entity\CapserPackage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CapserPackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CapserPackage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('capser-Paket')
            ->setEntityLabelInPlural('capser-Pakete');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name', 'Bezeichnung')
                ->setColumns(12)
                ->setRequired(true),
            DateField::new('validFrom', 'Gültig ab')
                ->setColumns(4)
                ->setRequired(true),
            DateField::new('validTo', 'Gültig bis')
                ->setColumns(4),
        ];
    }
}
