<?php

namespace App\Controller\Admin;

use App\Entity\Ticket;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TicketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ticket::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ticket')
            ->setEntityLabelInPlural('Tickets')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['title', 'description', 'client.name', 'assignedTo.email']);
    }

    public function configureFields(string $pageName): iterable
    {
        // --- CHAMPS PRINCIPAUX ---
        yield TextField::new('title', 'Sujet');

        yield TextareaField::new('description')
            ->hideOnIndex(); // Masqué dans la liste

        // --- CHAMPS DE STATUT (avec options et couleurs) ---
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Ouvert' => 'Ouvert',
                'En cours' => 'En cours',
                'Fermé' => 'Fermé',
            ])
            ->renderAsBadges([ // Affichage joli dans la liste
                'Ouvert' => 'success',
                'En cours' => 'warning',
                'Fermé' => 'danger',
            ]);

        yield ChoiceField::new('priority', 'Priorité')
            ->setChoices([
                'Faible' => 'Faible',
                'Moyenne' => 'Moyenne',
                'Urgente' => 'Urgente',
            ])
            ->renderAsBadges([
                'Faible' => 'info',
                'Moyenne' => 'warning',
                'Urgente' => 'danger',
            ]);

        // --- RELATIONS (Le correctif pour votre erreur) ---
        // Ce champ affichera une liste déroulante des Clients.
        yield AssociationField::new('client', 'Client');

        // Ce champ est pour le technicien.
        yield AssociationField::new('assignedTo', 'Assigné à (Technicien)');

        // --- HORODATAGE ---
        yield DateTimeField::new('createdAt', 'Créé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm(); // Masqué dans les formulaires (auto-généré)
    }
}
