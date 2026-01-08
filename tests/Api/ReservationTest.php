<?php

namespace App\Tests\Api;

use App\Entity\AvailabilitySlot;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationTest extends WebTestCase
{
    private $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }

    /**
     * Teste le scénario complet :
     * 1. Création d'un créneau disponible.
     * 2. Première réservation réussie (201 Created).
     * 3. Tentative de réserver le MÊME créneau (409 Conflict).
     */
    public function testDoubleBookingIsImpossible(): void
    {
        // --- ÉTAPE 1 : PRÉPARATION DES DONNÉES (FIXTURES) ---
        // On crée un technicien temporaire pour le test
        $tech = new User();
        $tech->setEmail('tech-test-' . uniqid() . '@zenivia.tech');
        $tech->setPassword('password123');
        $tech->setRoles(['ROLE_TECH']);
        $this->entityManager->persist($tech);

        // On crée un créneau de disponibilité pour demain
        $slot = new AvailabilitySlot();
        $slot->setTechnician($tech);
        $slot->setStartTime(new \DateTimeImmutable('+1 day 10:00'));
        $slot->setEndTime(new \DateTimeImmutable('+1 day 11:00'));
        $slot->setIsBooked(false); // Il est libre !

        $this->entityManager->persist($slot);
        $this->entityManager->flush();

        $slotId = $slot->getId(); // On garde l'ID pour nos requêtes

        // --- ÉTAPE 2 : PREMIÈRE RÉSERVATION (DOIT RÉUSSIR) ---
        $this->client->request(
            'POST',
            '/api/appointment/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'slotId' => $slotId,
                'name' => 'Premier Client',
                'email' => 'client1@test.com'
            ])
        );

        // On vérifie que le serveur répond "201 Created" (Succès)
        $this->assertResponseStatusCodeSame(201);

        // --- ÉTAPE 3 : DEUXIÈME RÉSERVATION (DOIT ÉCHOUER) ---
        // On réessaie exactement la même requête avec un autre client
        $this->client->request(
            'POST',
            '/api/appointment/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'slotId' => $slotId,
                'name' => 'Voleur de Créneau',
                'email' => 'voleur@test.com'
            ])
        );

        // on s'attend à une erreur 409 (Conflit)
        // Si le code renvoie 201 ou 500, le test échoue.
        $this->assertResponseStatusCodeSame(409);
    }
    /**
     * Teste la validation des données :
     * Envoie des données invalides (Email incorrect, Nom vide)
     * Doit retourner une erreur 400 (Bad Request) et non une 500.
     */
    public function testInvalidDataTriggersValidationError(): void
    {
        // --- PRÉPARATION (On a besoin d'un créneau valide pour tester l'envoi) ---
        $tech = new User();
        $tech->setEmail('tech-valid-' . uniqid() . '@zenivia.tech');
        $tech->setPassword('password123');
        $tech->setRoles(['ROLE_TECH']);
        $this->entityManager->persist($tech);

        $slot = new AvailabilitySlot();
        $slot->setTechnician($tech);
        $slot->setStartTime(new \DateTimeImmutable('+2 days 14:00')); // Autre date
        $slot->setEndTime(new \DateTimeImmutable('+2 days 15:00'));
        $slot->setIsBooked(false);

        $this->entityManager->persist($slot);
        $this->entityManager->flush();

        // --- TENTATIVE AVEC DONNÉES POURRIES  ---
        $this->client->request(
            'POST',
            '/api/appointment/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'slotId' => $slot->getId(),
                'name' => '',              // ERREUR : Nom vide (NotBlank)
                'email' => 'ceci-nest-pas-un-email' // ERREUR : Format invalide
            ])
        );

        // --- VÉRIFICATION ---
        // On veut une 400 Bad Request (Validation échouée)
        // Si il y as une 500, c'est que mon contrôleur ne gère pas encore proprement les erreurs de validation 
        $this->assertResponseStatusCodeSame(400);
    }
}
