<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Trajet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/api/reservations', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $trajet = $em->getRepository(Trajet::class)->find($data['trajet_id'] ?? 0);

        if (!$trajet) {
            return new JsonResponse(['error' => 'Trajet introuvable'], 404);
        }

        $nbPlaces = (int) ($data['nb_places'] ?? 1);
        if ($nbPlaces <= 0 || $nbPlaces > $trajet->getNbPlaces()) {
            return new JsonResponse(['error' => 'Nombre de places invalide'], 400);
        }

        $reservation = (new Reservation())
            ->setPassager($user)
            ->setTrajet($trajet)
            ->setNbPlacesReservees($nbPlaces);

        $trajet->setNbPlaces($trajet->getNbPlaces() - $nbPlaces);

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['message' => 'Réservation créée', 'id' => $reservation->getId()], 201);
    }

    #[Route('/api/mes-reservations', name: 'mes_reservations', methods: ['GET'])]
    public function mesReservations(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $reservations = $em->getRepository(Reservation::class)->findBy(['passager' => $user]);

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'trajet' => $r->getTrajet()->getId(),
            'nb_places' => $r->getNbPlacesReservees(),
        ], $reservations);

        return new JsonResponse($data);
    }
}
