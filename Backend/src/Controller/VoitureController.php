<?php

namespace App\Controller;

use App\Entity\Voiture;
use App\Repository\VoitureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

#[IsGranted('ROLE_USER')]
class VoitureController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/api/voiture', name: 'api_voiture_create', methods: ['POST'])]
    public function createVoiture(Request $request, VoitureRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        // Champs requis (⚠️ couleur est optionnelle)
        foreach (['marque','modele','immatriculation','energie','premiereImmat','places'] as $k) {
            if (empty($data[$k])) {
                return new JsonResponse(['error' => "Le champ '$k' est obligatoire"], 400);
            }
        }

        // Anti-doublon immatriculation
        if ($repo->findOneBy(['immatriculation' => $data['immatriculation']])) {
            return new JsonResponse(['error' => 'Cette immatriculation existe déjà'], 400);
        }

        $places = (int) $data['places'];
        if ($places <= 0) {
            return new JsonResponse(['error' => 'Nombre de places invalide'], 400);
        }

        try {
            $premiereImmat = new \DateTime($data['premiereImmat']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Format de date invalide (attendu: YYYY-MM-DD)'], 400);
        }

        $voiture = (new Voiture())
            ->setMarque($data['marque'])
            ->setModele($data['modele'])
            ->setImmatriculation($data['immatriculation'])
            ->setCouleur($data['couleur'] ?? null)
            ->setEnergie($data['energie'])
            ->setPremiereImmat($premiereImmat)
            ->setPlaces($places)
            ->setProprietaire($user);

        $this->em->persist($voiture);
        $this->em->flush();

        return new JsonResponse([
            'message' => 'Véhicule ajouté avec succès',
            'id'      => $voiture->getId()
        ], 201);
    }
#[Route('/api/mes-voitures', name: 'api_mes_voitures', methods: ['GET'])]
public function myCars(VoitureRepository $repo): JsonResponse
{
    $user = $this->getUser();
    $cars = $repo->findBy(['proprietaire' => $user], ['id' => 'DESC']);

    $out = array_map(function (Voiture $v) {
        return [
            'id'               => $v->getId(),
            'marque'           => $v->getMarque(),
            'modele'           => $v->getModele(),
            'immatriculation'  => $v->getImmatriculation(),
            'couleur'          => $v->getCouleur(),
            'energie'          => $v->getEnergie(),
            'premiereImmat'    => $v->getPremiereImmat()->format('Y-m-d'),
            'places'           => $v->getPlaces(),
        ];
    }, $cars);

    return new JsonResponse($out, Response::HTTP_OK);
}

// DELETE /api/voiture/{id} : supprime un véhicule (propriétaire uniquement)
#[Route('/api/voiture/{id}', name: 'api_voiture_delete', methods: ['DELETE'])]
public function deleteVoiture(Voiture $voiture): JsonResponse
{
    if ($voiture->getProprietaire()->getId() !== $this->getUser()->getId()) {
        return new JsonResponse(['error' => 'Accès interdit'], Response::HTTP_FORBIDDEN);
    }

    $this->em->remove($voiture);
    $this->em->flush();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
}
}