<?php

namespace App\Controller\Admin;

use App\Entity\AvailabilitySlot;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class AvailabilitySlotCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AvailabilitySlot::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // identifiant (affiché seulement dans la liste)
        yield IdField::new('id')->onlyOnIndex();

        // Technicien (Association vers User)
        yield AssociationField::new('technician', 'Technicien')
            ->setRequired(true);

        // Horaires
        yield DateTimeField::new('startTime', 'Début')
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('endTime', 'Fin')
            ->setFormat('dd/MM/yyyy HH:mm');

        // Statut réservation
        yield BooleanField::new('isBooked', 'Réservé ?');
    }

    /**
     * On s'assure que la contrainte NOT NULL est respectée avant persistance.
     * Si aucun technicien n'a été sélectionné dans le formulaire, on assigne l'utilisateur connecté.
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (! $entityInstance instanceof AvailabilitySlot) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        // Assigne le technicien courant s'il n'y en a pas (pratique si le technicien crée son propre créneau)
        if (null === $entityInstance->getTechnician()) {
            $user = $this->getUser();
            if (null !== $user) {
                $entityInstance->setTechnician($user);
            }
        }

        // Valeur par défaut pour isBooked
        if (null === $entityInstance->isBooked()) {
            $entityInstance->setIsBooked(false);
        }

        // Persister
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
