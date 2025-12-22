<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Les champs
        yield IdField::new('id')->onlyOnIndex(); // ID seulement dans la liste
        yield EmailField::new('email');
        yield ArrayField::new('roles') // Utilise ArrayField pour les rôles Symfony
            ->setLabel('Rôles');

        // Le champ password n'est affiché qu'à la création/édition pour des raisons de sécurité
        yield TextField::new('password')
            ->setLabel('Mot de passe')
            ->onlyOnForms() // Visible uniquement dans le formulaire de création/modification
            ->setRequired(true);

        yield DateTimeField::new('createdAt')
            ->setLabel('Créé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnDetail() // Seulement sur la page de détail (lecture seule)
            ->setPermission('ROLE_ADMIN'); // Visible uniquement par les ADMINs (si ce rôle existe)
    }
}
