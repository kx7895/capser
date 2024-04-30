<?php

namespace App\Controller\Admin;

use App\Entity\Country;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CountryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Country::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Land')
            ->setEntityLabelInPlural('Länder');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name')
                ->setColumns(12),
            TextField::new('state_name', 'Staatenname')
                ->setColumns(12),
            TextField::new('alpha2', 'ISO 3166-2 Letter')
                ->setColumns(6),
            TextField::new('alpha3', 'ISO 3166-3 Letter')
                ->setColumns(6),
        ];
    }
}
