<?php

namespace App\Controller\Admin;

use App\Entity\AccountingPlan;
use App\Entity\CapserPackage;
use App\Entity\CompanyType;
use App\Entity\Country;
use App\Entity\Currency;
use App\Entity\Customer;
use App\Entity\CustomerType;
use App\Entity\InvoiceType;
use App\Entity\Language;
use App\Entity\Principal;
use App\Entity\Tag;
use App\Entity\TermOfPayment;
use App\Entity\Unit;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setIcon('fa fa-plus')->setLabel(false))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action->setIcon('fas fa-edit')->setLabel(false))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->setIcon('fas fa-trash-alt')->setLabel(false))
            ->update(Crud::PAGE_DETAIL, Action::DELETE, fn (Action $action) => $action->setIcon('fas fa-trash-alt')->setLabel(false))
            ->update(Crud::PAGE_DETAIL, Action::INDEX, fn (Action $action) => $action->setIcon('fas fa-list')->setLabel(false))
            ->update(Crud::PAGE_DETAIL, Action::EDIT, fn (Action $action) => $action->setIcon('fas fa-edit')->setLabel(false))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setIcon('fas fa-save')->setLabel(false))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setIcon('far fa-edit')->setLabel(false))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setIcon('fas fa-save')->setLabel(false))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setIcon('far fa-edit')->setLabel(false))
            ->disable(Action::BATCH_DELETE);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('styles/admin.css');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->showEntityActionsInlined();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('capser <sup>admin</sup>');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::section('Basisdaten'),
            MenuItem::linkToCrud('capser-Pakete', null, CapserPackage::class),
            MenuItem::linkToCrud('Kundentyp', null, CustomerType::class),
            MenuItem::linkToCrud('Länder', null, Country::class),
            MenuItem::linkToCrud('Rechnungstyp', null, InvoiceType::class),
            MenuItem::linkToCrud('Sprachen', null, Language::class),
            MenuItem::linkToCrud('Unternehmensformen', null, CompanyType::class),
            MenuItem::linkToCrud('Währungen', null, Currency::class),

            MenuItem::section('Stammdaten'),
            MenuItem::linkToCrud('Benutzer', null, User::class),
            MenuItem::linkToCrud('Kunden', null, Customer::class),
            MenuItem::linkToCrud('Mandanten', null, Principal::class),
            MenuItem::linkToCrud('Mandanten-Kontenpläne', null, AccountingPlan::class),
            MenuItem::linkToCrud('Tags', null, Tag::class),
            MenuItem::linkToCrud('Rechnungseinheiten', null, Unit::class),
            MenuItem::linkToCrud('Zahlungsbedingungen', null, TermOfPayment::class),
        ];
    }

    public function configureUserMenu(UserInterface $user): UserMenu {
        return parent::configureUserMenu($user)
            ->setMenuItems([
                MenuItem::linkToUrl('capser', 'fa fa-paper-plane', '/'),
                MenuItem::linkToLogout('Abmelden', 'fa fa-sign-out'),
            ]);
    }

}
