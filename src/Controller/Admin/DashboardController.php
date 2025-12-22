<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Contact;
use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Entity\Appointment; // <-- AJOUTÉ
use App\Entity\AvailabilitySlot; // <-- AJOUTÉ
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    /**
     * Cette méthode est modifiée pour forcer la redirection vers la liste des Clients.
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // 2. Récupère le générateur d'URL EasyAdmin
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        // 3. Redirige l'utilisateur vers le ClientCrudController (la liste des Clients)
        return $this->redirect($adminUrlGenerator->setController(ClientCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // Nom de l'application visible en haut à gauche
            ->setTitle('Zenivia CRM'); // Changé de 'Zenivia' à 'Zenivia CRM' pour plus de clarté
    }

    public function configureMenuItems(): iterable
    {
        // 1. Lien vers la page d'accueil du Dashboard
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // 2. Section de Gestion CRM (Clients et Contacts)
        yield MenuItem::section('Gestion CRM');
        yield MenuItem::linkToCrud('Clients', 'fa fa-building', Client::class);
        yield MenuItem::linkToCrud('Contacts', 'fa fa-users', Contact::class);

        // --- SECTION TICKETING ---
        yield MenuItem::section('Support Technique');
        yield MenuItem::linkToCrud('Tickets', 'fa fa-ticket-alt', Ticket::class); // <-- Icône mise à jour
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', TicketComment::class);

        // --- NOUVELLE SECTION CALENDRIER (AJOUTÉE) ---
        yield MenuItem::section('Calendrier & RDV');
        yield MenuItem::linkToCrud('Rendez-vous', 'fa fa-calendar-check', Appointment::class);
        yield MenuItem::linkToCrud('Disponibilités', 'fa fa-calendar-alt', AvailabilitySlot::class);

        // 3. Section Sécurité
        yield MenuItem::section('Sécurité');
        yield MenuItem::linkToUrl('Déconnexion', 'fa fa-sign-out-alt', $this->generateUrl('app_logout'));
    }
}
