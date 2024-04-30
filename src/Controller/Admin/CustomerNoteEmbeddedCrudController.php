<?php

namespace App\Controller\Admin;

use App\Entity\CustomerNote;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CustomerNoteEmbeddedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerNote::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),

            TextField::new('title', 'Titel')
                ->setColumns(5),
            DateField::new('date', 'Datum')
                ->setColumns(3),
            FormField::addRow(),

            TextareaField::new('note', 'Notiz')
                ->setColumns(8),

            // TODO: Integration CustomerNoteAttachment.
        ];
    }
}
