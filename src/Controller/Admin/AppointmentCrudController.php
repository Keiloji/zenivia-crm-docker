<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class AppointmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Appointment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // id (visible seulement dans la liste)
        yield IdField::new('id')->onlyOnIndex();

        // Associations
        yield AssociationField::new('client', 'Client')
            ->setRequired(true);

        yield AssociationField::new('technician', 'Technicien')
            ->setRequired(true);

        // Horaires
        yield DateTimeField::new('startTime', 'Début')
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('endTime', 'Fin')
            ->setFormat('dd/MM/yyyy HH:mm');

        // Statut
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Programmé' => 'scheduled',
                'Annulé'    => 'cancelled',
                'Terminé'   => 'done',
            ]);
    }

    /**
     * Avant de persister, s'assurer que les contraintes NOT NULL sont satisfaites :
     * - si aucun technicien sélectionné, on assigne l'utilisateur connecté (pratique pour les techniciens)
     * - si pas de statut défini, on donne 'scheduled' par défaut
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (! $entityInstance instanceof Appointment) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        // Assigner technicien courant si absent
        if (null === $entityInstance->getTechnician()) {
            $user = $this->getUser();
            if (null !== $user) {
                $entityInstance->setTechnician($user);
            }
        }

        // Valeur par défaut pour le statut
        if (null === $entityInstance->getStatus()) {
            $entityInstance->setStatus('scheduled');
        }

        // Persister et flush
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
