<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController implements EventSubscriberInterface
{

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Benutzer')
            ->setEntityLabelInPlural('Benutzer')
            ->setDefaultSort(['lastName' => 'ASC', 'firstName' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $passwordField = TextField::new('plain_password', Crud::PAGE_NEW === $pageName ? 'Passwort' : 'Neues Passwort')
            ->setFormType(PasswordType::class)
            ->setColumns(6)
            ->hideOnIndex();
        if (Crud::PAGE_NEW === $pageName) {
            $passwordField->setRequired(true);
        }

        return [
            FormField::addTab('Basisangaben'),

            IdField::new('id')
                ->hideOnForm(),
            TextField::new('firstName', 'Vorname')
                ->setColumns(6),
            TextField::new('lastName', 'Nachname')
                ->setColumns(6),
            ImageField::new('profileImagePath', 'Profilfoto-Dateiname (PNG oder JPG)')
                ->setBasePath('images/profiles')
                ->setUploadDir('public/images/profiles')
                ->setHelp('Beispiel: <i>max.png</i> für <i>public/images/profiles/<u>max.jpg</u></i>')
                ->setColumns(6)
                ->hideOnIndex(),
            AssociationField::new('language', 'Sprache')
                ->hideOnIndex()
                ->setColumns(6)
                ->setFormTypeOptions(['placeholder' => '']),

            FormField::addFieldset('Kontaktdaten'),
            TextField::new('phone', 'Telefon')
                ->setColumns(6),

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

            $fields[] = FormField::addTab('Anmeldung'),

            BooleanField::new('disabled', 'Deaktiviert?')
                ->hideOnIndex()
                ->setColumns(12),

            TextField::new('email', 'E-Mail-Adresse')
                ->setColumns(6),
            FormField::addRow(),
            $passwordField,

            FormField::addTab('Berechtigungen'),
            ChoiceField::new('roles', 'Rollen')
                ->allowMultipleChoices()
                ->setChoices(fn() => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Super-Admin' => 'ROLE_SUPERADMIN'
                ])
                ->setColumns(12),
            AssociationField::new('principals', 'Mandanten')
                ->hideOnIndex()
                ->setColumns(12)
                ->setQueryBuilder(fn(QueryBuilder $queryBuilder) => $queryBuilder
                    ->orderBy('entity.name', 'ASC')
                )
                ->setFormTypeOption('choice_label', function($entity) {
                    return $entity->getName().' ('.$entity->getShortName().')';
                })
                ->setFormTypeOption('placeholder', ''),
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'hashPassword',
            BeforeEntityUpdatedEvent::class => 'hashPassword',
        ];
    }

    /**
     * @internal
     * @noinspection PhpUnused
     */
    public function hashPassword($event): void
    {
        $user = $event->getEntityInstance();
        if ($user instanceof User && $user->getPlainPassword()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPlainPassword()));
        }
    }
}
