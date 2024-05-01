<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Kunde')
            ->setEntityLabelInPlural('Kunden')
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('principal', 'Mandant'))
            ->add(TextFilter::new('name', 'Bezeichnung'))
            ->add(TextFilter::new('shortName', 'Kurz-Bezeichnung'))
            ->add(EntityFilter::new('customerType', 'Kundenart'))
        ;
    }
    public function configureFields(string $pageName): iterable
    {

        return [
            FormField::addTab('Basisangaben'),

            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('principal', 'Mandant')
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => $queryBuilder->orderBy('entity.name', 'ASC'))
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => '']),
            AssociationField::new('customerType', 'Kundentyp')
                ->setColumns(3)
                ->setFormTypeOptions(['placeholder' => '']),
            IntegerField::new('ledgerAccountNumber', 'Kundennummer')
                ->setColumns(3)
                ->setHelp('In Übereinstimmung mit dem Kontenplan.'),

            FormField::addFieldset('Name und Anschrift'),
            TextField::new('name', 'Bezeichnung')
                ->setColumns(8),
            TextField::new('shortName', 'Kurz-Bezeichnung')
                ->setColumns(4),
            TextField::new('addressLine1', 'Adresszeile 1')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('addressLine2', 'Adresszeile 2')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('addressLine3', 'Adresszeile 3')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('addressLine4', 'Adresszeile 4')
                ->setColumns(6)
                ->hideOnIndex(),
            AssociationField::new('addressLineCountry', 'Land')
                ->setColumns(6)
                ->hideOnIndex(),
            ImageField::new('logoPath', 'Logo-Dateiname (PNG oder JPG)')
                ->setBasePath('images/logos')
                ->setUploadDir('public/images/logos')
                ->setHelp('Beispiel: <i>atratoS.png</i> für <i>public/images/logos/<u>atratoS.png</u></i>')
                ->setColumns(6)
                ->hideOnIndex(),

            FormField::addFieldset('Kontakt'),
            CollectionField::new('customerInvoiceRecipients', 'Rechnungsempfänger (E-Mail)')
                ->hideOnIndex()
                ->setEntryIsComplex()
                ->useEntryCrudForm(CustomerInvoiceRecipientEmbeddedCrudController::class)
                ->setColumns(12),
            CollectionField::new('customerContactPersons', 'Kontaktpersonen')
                ->hideOnIndex()
                ->setEntryIsComplex()
                ->useEntryCrudForm(CustomerContactPersonEmbeddedCrudController::class)
                ->setColumns(12),

            FormField::addFieldset('Technische Daten'),
            IdField::new('id', 'ID')
                ->setRequired(false)
                ->setColumns(3)
                ->setDisabled()
                ->hideWhenCreating()
                ->onlyOnForms(),
            FormField::addRow(),
            DateTimeField::new('createdAt', 'Erstellt')
                ->setRequired(false)
                ->setColumns(3)
                ->setDisabled()
                ->hideWhenCreating()
                ->onlyOnForms(),
            DateTimeField::new('updatedAt', 'Letzte Änderung')
                ->setColumns(3)
                ->setDisabled()
                ->hideWhenCreating()
                ->onlyOnForms(),

            FormField::addTab('Rechnungsangaben'),

            TextField::new('vatId', 'USt.-Id.')
                ->hideOnIndex()
                ->setColumns(6),
            TextField::new('vatNumber', 'Steuernummer')
                ->hideOnIndex()
                ->setColumns(6),
            BooleanField::new('vatExemptInvoicesAllowed', 'USt.-freie Rechnungen?')
                ->setHelp('Soll es möglich sein, an diesen Kunden Rechnungen ohne USt. zu schreiben?')
                ->hideOnIndex()
                ->setColumns(6),

            FormField::addFieldset('Standardwerte')
                ->setHelp('Diese Standardwerte können bei der Belegerstellung individuell überschrieben werden.'),

            AssociationField::new('languageDefault', 'Sprache')
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => ''])
                ->hideOnIndex(),
            AssociationField::new('accountingPlanLedgerDefault', 'Buchungskonto')
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => ''])
                ->hideOnIndex(),
            AssociationField::new('currencyDefault', 'Währung')
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => ''])
                ->hideOnIndex(),
            AssociationField::new('termOfPaymentDefault', 'Zahlungsbedingung')
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => ''])
                ->hideOnIndex(),
            
            FormField::addFieldset('Fusszeile')
                ->setHelp('Sofern die Beleg-Fusszeile speziell für diesen Kunden angepasst werden soll, kann sie hiermit überschrieben werden.'),
            
            TextareaField::new('specialFooterColumn1', 'Fusszeile 1')
                ->setColumns(4)
                ->hideOnIndex(),
            TextareaField::new('specialFooterColumn2', 'Fusszeile 2')
                ->setColumns(4)
                ->hideOnIndex(),
            TextareaField::new('specialFooterColumn3', 'Fusszeile 3')
                ->setColumns(4)
                ->hideOnIndex(),

            FormField::addTab('Konto-/Lastschriftangaben'),

            TextField::new('bankAccountIban', 'IBAN')
                ->hideOnIndex()
                ->setColumns(6),
            TextField::new('bankAccountBic', 'BIC')
                ->hideOnIndex()
                ->setColumns(6),
            TextField::new('bankAccountHolder', 'Kontoinhaber')
                ->hideOnIndex()
                ->setColumns(6),
            TextField::new('bankAccountBank', 'Institut')
                ->hideOnIndex()
                ->setColumns(6),
            TextField::new('bankDirectAuthorizationNumber', 'Referenz Lastschriftmandat')
                ->hideOnIndex()
                ->setColumns(6),
            DateField::new('bankDirectAuthorizationDate', 'Unterschrift Lastschriftmandat')
                ->hideOnIndex()
                ->setColumns(6),

            FormField::addTab('Notizen'),
            CollectionField::new('customerNotes', 'CRM-Notizen')
                ->hideOnIndex()
                ->setEntryIsComplex()
                ->useEntryCrudForm(CustomerNoteEmbeddedCrudController::class)
                ->setColumns(12),

            FormField::addTab('Weitere Informationen'),

            AssociationField::new('tags', 'Tags')
                ->setColumns(12)
                ->setFormTypeOptions(['placeholder' => ''])
                ->hideOnIndex(),

        ];
    }

}
