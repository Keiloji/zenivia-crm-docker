<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;

class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    /**
     * Configuration globale du CRUD (titres, tri par défaut, champs de recherche).
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Client')
            ->setEntityLabelInPlural('Clients')
            // Définir les champs consultables
            ->setSearchFields(['name', 'country', 'city', 'postalCode'])
            // Trier par ID par défaut (le plus récent en premier)
            ->setDefaultSort(['id' => 'DESC']);
    }

    /**
     * Configure les champs à afficher sur les différentes pages (Index, Formulaires, Détail).
     */
    public function configureFields(string $pageName): iterable
    {
        // --- CHAMPS POUR LA PAGE INDEX (LISTE) ---

        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('name', 'Nom du Client');
            yield TextField::new('country', 'Pays');
            yield EmailField::new('email', 'Email du Contact');

            // Ce champ spécial utilise un template pour afficher le *nombre* de contacts
            yield CollectionField::new('contacts', 'Nb Contacts')
                ->setTemplatePath('admin/fields/contacts_count.html.twig')
                ->setSortable(false);
        }

        // --- CHAMPS POUR LES FORMULAIRES (CRÉATION / ÉDITION) ---
        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('name', 'Nom du Client');

            yield EmailField::new('email', 'Email du Contact');

            yield TextareaField::new('description', 'Description')
                ->setHelp('Description interne de l\'entreprise.');
            yield TextField::new('city', 'Ville');
            yield TextField::new('postalCode', 'Code Postal');
            yield TextField::new('country', 'Pays');
        }

        // --- CHAMPS POUR LA PAGE DE DÉTAIL ---
        if (Crud::PAGE_DETAIL === $pageName) {
            yield IdField::new('id');
            yield TextField::new('name', 'Nom du Client');
            yield TextareaField::new('description');
            yield TextField::new('city', 'Ville');
            yield TextField::new('postalCode', 'Code Postal');
            yield TextField::new('country', 'Pays');

            // Affiche la *liste* réelle des contacts associés
            yield AssociationField::new('contacts', 'Liste des Contacts')
                ->setTemplatePath('admin/fields/contacts_detail.html.twig'); // un meilleur affichage
        }
    }
}
