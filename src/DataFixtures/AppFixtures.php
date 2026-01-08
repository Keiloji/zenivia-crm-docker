<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // --- 1. CRÉATION DES UTILISATEURS ---
        
        // Ton Admin (Indispensable pour se connecter)
        $admin = new User();
        $admin->setEmail('zeniviacom@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Mamba'));
        $manager->persist($admin);

        // Un Technicien (Pour tester l'assignation des tickets)
        $tech = new User();
        $tech->setEmail('tech@zenivia.com');
        $tech->setRoles(['ROLE_USER']);
        $tech->setPassword($this->passwordHasher->hashPassword($tech, 'password'));
        $manager->persist($tech);

        // --- 2. CRÉATION DES CLIENTS ---
        
        $clients = []; // On garde une liste pour pouvoir y lier des tickets après

        for ($i = 1; $i <= 5; $i++) {
            $client = new Client();
            $client->setName("Entreprise n°$i");
            $client->setEmail("contact@entreprise$i.com");
            $client->setCity("Paris");
            $client->setPostalCode("7500$i");
            $client->setCountry("France");
            $client->setDescription("Client fidèle depuis 202$i.");

            $manager->persist($client);
            $clients[] = $client; // Ajout au tableau
        }

        // --- 3. CRÉATION DES TICKETS ---
        
        $statuses = ['Nouveau', 'En cours', 'Résolu'];
        $priorities = ['Faible', 'Moyenne', 'Urgente'];

        for ($j = 1; $j <= 10; $j++) {
            $ticket = new Ticket();
            $ticket->setTitle("Problème technique n°$j");
            $ticket->setDescription("Description détaillée du ticket n°$j pour tester l'API.");
            
            // Choix aléatoire
            $ticket->setStatus($statuses[array_rand($statuses)]);
            $ticket->setPriority($priorities[array_rand($priorities)]);

            // Lier à un client au hasard (Relation ManyToOne)
            $ticket->setClient($clients[array_rand($clients)]);

            // Assigner soit à l'Admin, soit au Tech (un ticket sur deux)
            $assignedUser = ($j % 2 === 0) ? $tech : $admin;
            $ticket->setAssignedTo($assignedUser);

            $manager->persist($ticket);
        }

        $manager->flush();
    }
}