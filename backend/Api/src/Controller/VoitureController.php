<?php

namespace App\Controller;

use App\Entity\Voiture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoitureController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/api/voiture', name: 'api_voiture_create', methods: ['POST'])]
    public function createVoiture(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        try {
            $voiture = new Voiture();
            $voiture
                ->setMarque($data['marque'] ?? '')
                ->setModele($data['modele'] ?? '')
                ->setImmatriculation($data['immatriculation'] ?? '')
                ->setPlaces($data['places'] ?? 0)
                ->setProprietaire($user);

            $this->em->persist($voiture);
            $this->em->flush();

            return new JsonResponse(['success' => 'Voiture ajoutée'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}
