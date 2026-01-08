<?php
// src/Controller/Api/AppointmentApiController.php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Client; // AJOUTÉ
use App\Repository\AvailabilitySlotRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api/appointment', name: 'api_appointment_')]
class AppointmentApiController extends AbstractController
{
    #[Route('/book', name: 'book', methods: ['POST'])]
    public function book(
        Request $request,
        AvailabilitySlotRepository $slotRepo,
        ClientRepository $clientRepo,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // 1. Validation de base : on veut au moins un slotId, un nom et un email
        if (empty($data['slotId']) || empty($data['name']) || empty($data['email'])) {
            return new JsonResponse(['error' => 'Veuillez remplir tous les champs (Nom, Email, Créneau).'], 400);
        }

        // 2. Vérifier le créneau
        $slot = $slotRepo->find((int)$data['slotId']);
        if (!$slot) {
            return new JsonResponse(['error' => 'Créneau introuvable.'], 404);
        }
        if ($slot->isBooked()) {
            return new JsonResponse(['error' => 'Ce créneau est déjà réservé.'], 409);
        }

        // 3. Trouver ou Créer le Client
        $client = $clientRepo->findOneBy(['email' => $data['email']]);

        if (!$client) {
            // Nouveau client
            $client = new Client();
            $client->setEmail($data['email']);
            $client->setName($data['name']);
            // Valeurs par défaut pour éviter les erreurs SQL (si champs obligatoires)
            $client->setCity('Non renseigné');
            $client->setPostalCode('00000');
            $client->setCountry('France');

            $em->persist($client);
        } else {
            // Client existant : on met à jour son nom si besoin
            $client->setName($data['name']);
        }

        // 4. Créer le Rendez-vous
        $appointment = new Appointment();
        $appointment->setClient($client);
        $appointment->setTechnician($slot->getTechnician());
        $appointment->setStartTime($slot->getStartTime());
        $appointment->setEndTime($slot->getEndTime());
        $appointment->setStatus('scheduled');

        // 5. Marquer le créneau comme pris
        $slot->setIsBooked(true);

        // 6. Sauvegarder
        $em->persist($appointment);
        $em->flush();

        // 7. Envoyer les emails
        $this->sendConfirmationEmails($mailer, $appointment, $client, $slot->getTechnician());

        return new JsonResponse([
            'message' => 'Rendez-vous confirmé ! Un email vous a été envoyé.',
            'id' => $appointment->getId()
        ], 201);
    }

    private function sendConfirmationEmails(MailerInterface $mailer, Appointment $appointment, Client $client, $technician)
    {
        $dateStr = $appointment->getStartTime()->format('d/m/Y à H:i');
        $meetLink = "https://meet.google.com/new"; // Lien générique

        // Email pour le Client
        $emailClient = (new Email())
            ->from('no-reply@zenivia.tech')
            ->to($client->getEmail())
            ->subject('Confirmation de RDV - Zenivia')
            ->text(
                "Bonjour {$client->getName()},\n\n" .
                    "Votre rendez-vous est confirmé pour le {$dateStr}.\n" .
                    "Lien de visio : {$meetLink}\n\n" .
                    "Merci de votre confiance,\nL'équipe Zenivia."
            );
        $mailer->send($emailClient);

        // Email pour le Technicien (si le technicien a un email)
        if ($technician && method_exists($technician, 'getEmail')) {
            $emailTech = (new Email())
                ->from('no-reply@zenivia.tech')
                ->to($technician->getEmail())
                ->subject('Nouveau RDV : ' . $client->getName())
                ->text(
                    "Nouveau RDV planifié !\n\n" .
                        "Client : {$client->getName()} ({$client->getEmail()})\n" .
                        "Date : {$dateStr}\n"
                );
            $mailer->send($emailTech);
        }
    }
}
