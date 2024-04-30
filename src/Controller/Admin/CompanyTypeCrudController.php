<?php

namespace App\Controller\Admin;

use App\Entity\CompanyType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompanyTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompanyType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Unternehmensform')
            ->setEntityLabelInPlural('Unternehmensformen');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name', 'Bezeichnung')
                ->setColumns(8),
            TextField::new('abbreviation', 'Abkürzung')
                ->setColumns(4),
        ];
    }
}
