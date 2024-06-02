<?php

namespace App\Controller\Admin;

use App\Entity\Principal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class PrincipalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Principal::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mandant')
            ->setEntityLabelInPlural('Mandanten')
            ->setDefaultSort(['name' => 'ASC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Bezeichnung'))
            ->add(TextFilter::new('shortName', 'Kurz-Bezeichnung'))
            ->add(EntityFilter::new('capserPackage', 'capser-Paket'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Basisangaben'),

            IdField::new('id')
                ->hideOnForm(),
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
                ->setColumns(6),

            FormField::addFieldset('Kontaktangaben'),
            TextField::new('phone', 'Telefon')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('fax', 'Fax')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('email', 'E-Mail')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('website', 'Webseite')
                ->setColumns(6)
                ->hideOnIndex(),

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

            ImageField::new('logoPath', 'Logo-Dateiname (PNG oder JPG)')
                ->setBasePath('images/logos')
                ->setUploadDir('public/images/logos')
                ->setHelp('Beispiel: <i>atratoS.png</i> für <i>public/images/logos/<u>atratoS.png</u></i>')
                ->setColumns(6)
                ->hideOnIndex(),
            IntegerField::new('fibuDocumentNumberRange', 'Rechnungsnummernkreis')
                ->setHelp('Beispiel: <i>4270</i> für <i>42700001</i>')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('fibuRecipientEmail1', 'Rechnungsempfänger (FiBu) 1')
                ->setHelp('An diese E-Mail-Adresse werden sämtliche erstellten Belege automatisch (in Bcc) gesandt')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('fibuRecipientEmail2', 'Rechnungsempfänger (FiBu) 2')
                ->setHelp('An diese E-Mail-Adresse werden sämtliche erstellten Belege automatisch (in Bcc) gesandt')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('fibuRecipientEmail3', 'Rechnungsempfänger (FiBu) 3')
                ->setHelp('An diese E-Mail-Adresse werden sämtliche erstellten Belege automatisch (in Bcc) gesandt')
                ->setColumns(6)
                ->hideOnIndex(),

            FormField::addFieldset('Rechnungsangaben (deutsch)'),

            TextareaField::new('footerColumn1', 'Fusszeile 1')
                ->setColumns(4)
                ->hideOnIndex(),
            TextareaField::new('footerColumn2', 'Fusszeile 2')
                ->setColumns(4)
                ->hideOnIndex(),
            TextareaField::new('footerColumn3', 'Fusszeile 3')
                ->setColumns(4)
                ->hideOnIndex(),

            FormField::addFieldset('Rechnungsangaben (englisch)'),

            TextareaField::new('footerColumn1En', 'Fusszeile 1')
                ->setColumns(4)
                ->hideOnIndex(),
            TextareaField::new('footerColumn2En', 'Fusszeile 2')
                ->setColumns(4)
                ->hideOnIndex(),
            TextareaField::new('footerColumn3En', 'Fusszeile 3')
                ->setColumns(4)
                ->hideOnIndex(),

            FormField::addFieldset('Weitere Nummernkreise'),
            IntegerField::new('customerNumberRange', 'Kundennummern')
                ->setHelp('Beispiel: <i>15</i> für <i>15001</i>')
                ->setColumns(6)
                ->hideOnIndex(),
            IntegerField::new('supplierNumberRange', 'Lieferantennummern')
                ->setHelp('Beispiel: <i>70</i> für <i>70001</i>')
                ->setColumns(6)
                ->hideOnIndex(),

            FormField::addTab('Steuerangaben'),

            AssociationField::new('vatCompanyType', 'Unternehmensart')
                ->setColumns(12),

            TextField::new('vatId', 'USt-IdNr.')
                ->setColumns(6)
                ->hideOnIndex(),
            TextField::new('vatNumber', 'Steuernummer')
                ->setColumns(6)
                ->hideOnIndex(),
            BooleanField::new('vatExempt', 'Steuerbefreit?')
                ->setColumns(6)
                ->hideOnIndex(),
            FormField::addRow(),
            ChoiceField::new('vatReportCalculation', 'Besteuerungsart')
                ->setChoices([
                    'USt. wird erst mit Zahlungseingang abgeführt (Ist-Versteuerung)' => 'IstVersteuerung',
                    'USt. wird sofort mit Rechnungsstellung abgeführt (Soll-Versteuerung)' => 'SollVersteuerung',
                    'Grundsätzlich umsatzsteuerbefreit' => 'KeineVersteuerung',
                ])
                ->renderExpanded()
                ->setColumns(12)
                ->hideOnIndex(),
            ChoiceField::new('vatReportInterval', 'Umsatzsteuervoranmeldung')
                ->setChoices([
                    'Monatlich' => 'Monat',
                    'Vierteljährlich' => 'Quartal',
                    'Jährlich' => 'Jahr',
                    'Keine' => 'Keine',
                ])
                ->renderExpanded()
                ->setColumns(12)
                ->hideOnIndex(),
            AssociationField::new('accountingPlan', 'Kontenplan')
                ->setColumns(12)
                ->setFormTypeOptions(['placeholder' => '']),

            FormField::addTab('capser-Abonnement'),

            AssociationField::new('capserPackage', 'Gebuchtes Paket')
                ->setColumns(6),
            AssociationField::new('mainContact', 'Ansprechpartner')
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => '']),

            FormField::addTab('Zuordnungen')
                ->hideOnForm(),

            AssociationField::new('users', 'Benutzer')
                ->setSortable(false)
                ->hideOnForm(),
        ];
    }

}
