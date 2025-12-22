<?php

namespace App\Controller\Admin;

use App\Entity\TicketComment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class TicketCommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TicketComment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commentaire')
            ->setEntityLabelInPlural('Commentaires')
            // Trie par date de création (ASC pour voir le plus ancien en premier, comme un chat)
            ->setDefaultSort(['createdAt' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        // --- RELATIONS ---

        // Affiche le ticket parent
        // Le setAutocomplete() a été retiré pour corriger l'erreur Intelephense
        yield AssociationField::new('ticket', 'Ticket');

        // Affiche l'auteur
        // Le setAutocomplete() a été retiré pour corriger l'erreur Intelephense
        yield AssociationField::new('author', 'Auteur');

        // --- CONTENU ---
        yield TextareaField::new('content', 'Contenu')
            ->hideOnIndex(); // Masqué dans la liste pour garder la vue propre

        // --- HORODATAGE ---
        yield DateTimeField::new('createdAt', 'Posté le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm(); // Masqué dans le formulaire (auto-généré par l'entité)
    }
}
