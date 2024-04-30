<?php

namespace App\Controller\Admin;

use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tag')
            ->setEntityLabelInPlural('Tags')
            ->setHelp('index', 'Tags sind Mandanten-spezifisch und können dort u.a. zur Kategorisierung von Kunden und Rechnungen genutzt werden. Zudem können allgemeingültige Tags (ohne Mandanten-Zuordnung) definiert werden.');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('principal', 'Mandant')
                ->setHelp('Wenn kein Mandant angegeben wird, steht dieses Tag in allen Mandanten zur Verfügung.')
                ->setColumns(12)
                ->setFormTypeOptions(['placeholder' => '']),
            TextField::new('name', 'Bezeichnung')
                ->setColumns(12),
        ];
    }
}
