<?php

namespace App\Controller\Admin;

use App\Entity\AccountingPlan;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AccountingPlanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AccountingPlan::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kontenplan')
            ->setEntityLabelInPlural('Kontenpläne')
            ->setHelp('index', 'Zwischen Kontenplan und Mandant besteht immer zwingend eine 1:1-Beziehung.');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('principal', 'Mandant')
                ->setColumns(12)
                ->setFormTypeOptions(['placeholder' => '']),
            TextField::new('name', 'Bezeichnung')
                ->setColumns(12),
        ];
    }
}
