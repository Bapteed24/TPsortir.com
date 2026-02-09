<?php

namespace App\Controller\Admin;

use App\Entity\Campus;
use App\Entity\User;
use App\Entity\Ville;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user || !$user->isAdmin()) {
            throw $this->createAccessDeniedException();
        }

       return $this->redirectToRoute('admin_user_index');

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('TPsortir');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Campus', 'fa fa-building', Campus::class);
        yield MenuItem::linkToCrud('Villes', 'fa fa-map', Ville::class);
    }
}
