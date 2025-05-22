<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ReservationController extends AbstractController
{
    #[Route('/api/reservations', name: 'api_reservation_create', methods: ['POST'])]
    #[IsGranted('ROLE_PASSAGER')]
    public function create(Request $request, EntityManagerInterface $em, TrajetRepository $trajetRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $trajetId = $data['trajetId'] ?? null;
        $nbPlaces = isset($data['places']) ? (int)$data['places'] : 1;

        if (!$trajetId) {
            return new JsonResponse(['message' => 'trajetId est requis'], 400);
        }

        $trajet = $trajetRepository->find($trajetId);
        if (!$trajet) {
            return new JsonResponse(['message' => 'Trajet non trouvé'], 404);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non authentifié'], 401);
        }

        // Vérification des places restantes
        $placesRestantes = $trajet->getPlacesDisponibles();
        if ($nbPlaces > $placesRestantes) {
            return new JsonResponse(['message' => 'Pas assez de places disponibles'], 400);
        }

        // Création de la réservation
        $reservation = new Reservation();
        $reservation->setPassager($user);
        $reservation->setTrajet($trajet);
        $reservation->setNbPlacesReservees($nbPlaces);

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['message' => 'Réservation créée'], 201);
    }

    

    #[Route('/api/mes-reservations', name: 'api_reservations_user', methods: ['GET'])]
#[IsGranted('ROLE_PASSAGER')]
public function mesReservations(SerializerInterface $serializer): JsonResponse
{
    $user = $this->getUser();

    if (!$user) {
        return new JsonResponse(['error' => 'Non authentifié'], 401);
    }

    // Récupère les réservations du passager connecté
    $reservations = $user->getReservations();

    // Sérialise avec groupes pour éviter les boucles infinies
    $json = $serializer->serialize(
        $reservations,
        'json',
        [AbstractNormalizer::GROUPS => ['reservation:read']]
    );

    return new JsonResponse($json, 200, [], true);
}    
}
