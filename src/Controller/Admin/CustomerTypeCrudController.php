<?php

namespace App\Controller\Admin;

use App\Entity\CustomerType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kundentyp')
            ->setEntityLabelInPlural('Kundentyp');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name')
                ->setColumns(12),
        ];
    }
}
