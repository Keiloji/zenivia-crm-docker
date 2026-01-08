<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Contact;
use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Entity\Appointment;
use App\Entity\AvailabilitySlot;
use Doctrine\ORM\EntityManagerInterface; // <--- IMPORTANT : Pour parler à la BDD
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    // On déclare la variable pour stocker le gestionnaire d'entités
    private EntityManagerInterface $entityManager;

    // Le constructeur permet à Symfony de nous donner l'accès à la BDD
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // 1. On compte les Rendez-vous
        // (Si tu n'as pas encore de tickets, tu peux mettre 0 à la place du count pour tester)
        $appointments = $this->entityManager->getRepository(Appointment::class)->count([]);

        $tickets = 0;
        // Décommente la ligne ci-dessous quand tu auras créé des tickets
        $tickets = $this->entityManager->getRepository(Ticket::class)->count([]);

        // 2. On affiche la vue Twig en lui passant les chiffres
        return $this->render('admin/dashboard.html.twig', [
            'countAppointments' => $appointments,
            'countTickets' => $tickets,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Zenivia CRM')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion CRM');
        yield MenuItem::linkToCrud('Clients', 'fa fa-building', Client::class);
        yield MenuItem::linkToCrud('Contacts', 'fa fa-users', Contact::class);

        yield MenuItem::section('Support Technique');
        yield MenuItem::linkToCrud('Tickets', 'fa fa-ticket-alt', Ticket::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', TicketComment::class);

        yield MenuItem::section('Calendrier & RDV');
        yield MenuItem::linkToCrud('Rendez-vous', 'fa fa-calendar-check', Appointment::class);
        yield MenuItem::linkToCrud('Disponibilités', 'fa fa-calendar-alt', AvailabilitySlot::class);

        yield MenuItem::section('Sécurité');
        yield MenuItem::linkToUrl('Déconnexion', 'fa fa-sign-out-alt', $this->generateUrl('app_logout'));
    }
}
