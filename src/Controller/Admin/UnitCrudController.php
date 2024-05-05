<?php

namespace App\Controller\Admin;

use App\Entity\Unit;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UnitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Unit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Einheit')
            ->setEntityLabelInPlural('Einheiten')
            ->setHelp('index', 'Einheiten sind Mandanten-spezifisch und können dort für Rechnungen genutzt werden. Allgemeingültige Tags (ohne Mandanten-Zuordnung) werden als Vorlage für neu angelegte Mandanten verwendet.');
    }

    public function createEntity(string $entityFqcn): Unit
    {
        $entity = parent::createEntity($entityFqcn);
        $entity->setCreatedAt(new DateTimeImmutable());
        $entity->setCreatedBy($this->getUser());
        return $entity;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setUpdatedAt(new DateTime());
        $entityInstance->setUpdatedBy($this->getUser());
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('principal', 'Mandant')
                ->setHelp('Wenn kein Mandant angegeben wird, steht diese Einheit als Vorlage in neu angelegten Mandanten zur Verfügung.')
                ->setColumns(12)
                ->setFormTypeOptions(['placeholder' => '']),
            FormField::addFieldset('Bezeichnungen'),
            TextField::new('name', 'DE')
                ->setColumns(6)
                ->setRequired(true),
            TextField::new('nameEn', 'EN')
                ->setColumns(6),
            TextField::new('nameFr', 'FR')
                ->setColumns(6),
            TextField::new('nameIt', 'IT')
                ->setColumns(6),
        ];
    }
}
