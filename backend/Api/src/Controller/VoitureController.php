<?php

namespace App\Controller;

use App\Entity\Voiture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class VoitureController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/api/voiture', name: 'api_voiture_create', methods: ['POST'])]
    public function createVoiture(Request $request): JsonResponse
    {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        if (empty($data['marque']) || empty($data['modele']) || empty($data['immatriculation'])) {
            return new JsonResponse(['error' => 'Tous les champs sont obligatoires'], 400);
        }

        $places = (int) ($data['places'] ?? 0);
        if ($places <= 0) {
            return new JsonResponse(['error' => 'Nombre de places invalide'], 400);
        }

        $voiture = (new Voiture())
            ->setMarque($data['marque'])
            ->setModele($data['modele'])
            ->setImmatriculation($data['immatriculation'])
            ->setPlaces($places)
            ->setProprietaire($user);

        $this->em->persist($voiture);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Voiture ajoutée',
            'id' => $voiture->getId()
        ], 201);
    }
}
