<?php

namespace App\Controller\Admin;

use App\Entity\TermOfPayment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TermOfPaymentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TermOfPayment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zahlungsbedingung')
            ->setEntityLabelInPlural('Zahlungsbedingungen')
            ->setHelp('index', 'Zahlungsbedingungen sind Mandanten-spezifisch und werden dort zur Rechnungsstellung genutzt werden.');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Basisangaben'),

            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('principal', 'Mandant')
                ->setColumns(12)
                ->setFormTypeOptions(['placeholder' => '']),
            TextField::new('name', 'Bezeichnung')
                ->setColumns(6),
            IntegerField::new('dueDays', 'Zahlungsfrist')
                ->setHelp('Angabe in Tagen ab Belegdatum')
                ->setColumns(6),

            FormField::addFieldset('Rechnungsangaben'),

            TextareaField::new('text', 'Deutsch')
                ->setColumns(6)
                ->hideOnIndex(),
            TextareaField::new('textEn', 'Englisch')
                ->setColumns(6)
                ->hideOnIndex(),
        ];
    }
}
