<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Assessment;
use App\Entity\Category;
use App\Entity\Child;
use App\Entity\SchoolClass;
use App\Entity\Score;
use App\Entity\Stat;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $stats = [
            'children' => $this->entityManager->getRepository(Child::class)->count([]),
            'schoolClasses' => $this->entityManager->getRepository(SchoolClass::class)->count([]),
            'assessments' => $this->entityManager->getRepository(Assessment::class)->count([]),
            'scores' => $this->entityManager->getRepository(Score::class)->count([]),
            'themes' => $this->entityManager->getRepository(Theme::class)->count([]),
            'categories' => $this->entityManager->getRepository(Category::class)->count([]),
            'users' => $this->entityManager->getRepository(User::class)->count([]),
            'stat' => $this->entityManager->getRepository(Stat::class)->count([]),
        ];

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Gestion des Notes');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Classes', 'fa fa-calendar', SchoolClass::class);
        yield MenuItem::linkToCrud('Enfants', 'fa fa-child', Child::class);
        yield MenuItem::section('Évaluations');
        yield MenuItem::linkToCrud('Évaluations', 'fa fa-clipboard-list', Assessment::class);
        yield MenuItem::linkToCrud('Notes', 'fa fa-star', Score::class);
        yield MenuItem::section('Thèmes');
        yield MenuItem::linkToCrud('Thèmes', 'fa fa-tag', Theme::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-book', Category::class);
        yield MenuItem::section('Statistiques');
        yield MenuItem::linkToCrud('Statistiques', 'fa fa-chart-area', Stat::class);
        yield MenuItem::section('Sécurité');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToUrl('Frontend', 'fa fa-globe', '/');
        yield MenuItem::linkToUrl('Logout', 'fa-solid fa-arrow-right-to-bracket', '/logout');
    }
}
