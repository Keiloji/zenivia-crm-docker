<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
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
     * Configuration globale du CRUD.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Client')
            ->setEntityLabelInPlural('Clients')
            ->setSearchFields(['name', 'country', 'city', 'postalCode'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    /**
     * Optimisation majeure : On récupère les relations en une seule requête SQL (Join)
     * au lieu d'une requête par ligne (N+1 Problem). Cela fluidifie l'affichage.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.contacts', 'c')
            ->addSelect('c')
            ->leftJoin('entity.appointments', 'a')
            ->addSelect('a');
    }

    public function configureFields(string $pageName): iterable
    {
        // --- PAGE INDEX (LISTE) ---
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('name', 'Nom du Client');
            yield TextField::new('country', 'Pays');
            yield EmailField::new('email', 'Email du Contact');

            yield CollectionField::new('contacts', 'Nb Contacts')
                ->setTemplatePath('admin/fields/contacts_count.html.twig')
                ->setSortable(false);
        }

        // --- FORMULAIRES (CRÉATION / ÉDITION) ---
        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('name', 'Nom du Client');
            yield EmailField::new('email', 'Email du Contact');
            yield TextareaField::new('description', 'Description')
                ->setHelp('Description interne de l\'entreprise.');
            yield TextField::new('city', 'Ville');
            yield TextField::new('postalCode', 'Code Postal');
            yield TextField::new('country', 'Pays');
        }

        // --- PAGE DE DÉTAIL ---
        if (Crud::PAGE_DETAIL === $pageName) {
            yield IdField::new('id');
            yield TextField::new('name', 'Nom du Client');
            yield TextareaField::new('description');
            yield TextField::new('city', 'Ville');
            yield TextField::new('postalCode', 'Code Postal');
            yield TextField::new('country', 'Pays');

            yield AssociationField::new('contacts', 'Liste des Contacts')
                ->setTemplatePath('admin/fields/contacts_detail.html.twig');
        }
    }
}
