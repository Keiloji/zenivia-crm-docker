<?php

namespace App\Controller\Api;

use App\Repository\AvailabilitySlotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AvailabilityController extends AbstractController
{
    /**
     * TÂCHE 13 : Endpoint GET pour lister les créneaux disponibles.
     */
    #[Route('/api/availability', name: 'api_availability_list', methods: ['GET'])]
    public function index(AvailabilitySlotRepository $availabilitySlotRepository): JsonResponse
    {
        // Utilise la méthode du Repository créée à l'étape 2
        $availableSlots = $availabilitySlotRepository->findAvailableSlots();

        // Retourne les données au format JSON
        // 'default' est utilisé pour la sérialisation de base, 
        //  besoin de 'groups' plus tard si l'objet est complexe.
        return $this->json($availableSlots, 200, [], ['groups' => 'slot:read']);
    }
}
