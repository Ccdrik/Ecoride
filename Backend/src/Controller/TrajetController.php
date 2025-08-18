<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CHAUFFEUR')]
class TrajetController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    // POST /api/trajets : créer un trajet
    #[Route('/api/trajets', name: 'api_trajet_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        foreach (['villeDepart','villeArrivee','dateDepart','nbPlaces','prix'] as $k) {
            if (empty($data[$k])) {
                return new JsonResponse(['error' => "Champ manquant: $k"], 400);
            }
        }

        try {
            $date = new \DateTimeImmutable($data['dateDepart']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Format dateDepart invalide (attendu: YYYY-MM-DDTHH:MM:SS)'], 400);
        }

        $trajet = (new Trajet())
            ->setVilleDepart($data['villeDepart'])
            ->setVilleArrivee($data['villeArrivee'])
            ->setDateDepart($date)
            ->setNbPlaces((int) $data['nbPlaces'])
            ->setPrix((float) $data['prix'])
            ->setEcologique(!empty($data['ecologique']))
            ->setChauffeur($user);

        $this->em->persist($trajet);
        $this->em->flush();

        return new JsonResponse([
            'id' => $trajet->getId(),
            'message' => 'Trajet créé',
        ], 201);
    }

    // GET /api/trajets?depart=...&arrivee=...&date=YYYY-MM-DD
    #[Route('/api/trajets', name: 'api_trajet_list', methods: ['GET'])]
    public function list(Request $request, TrajetRepository $repo): JsonResponse
    {
        $depart  = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $date    = $request->query->get('date'); // optionnel

        $dateObj = null;
        if ($date) {
            try { $dateObj = new \DateTimeImmutable($date.' 00:00:00'); } catch (\Exception $e) {}
        }

        $trajets = $repo->search($depart, $arrivee, $dateObj);

        $out = array_map(fn(Trajet $t) => [
            'id'            => $t->getId(),
            'villeDepart'   => $t->getVilleDepart(),
            'villeArrivee'  => $t->getVilleArrivee(),
            'dateDepart'    => $t->getDateDepart()->format('Y-m-d H:i'),
            'nbPlaces'      => $t->getNbPlaces(),
            'prix'          => $t->getPrix(),
            'ecologique'    => $t->isEcologique(),
            'actif'         => $t->isActif(),
            'chauffeur'     => [
                'id' => $t->getChauffeur()->getId(),
                'pseudo' => $t->getChauffeur()->getPseudo(),
            ],
        ], $trajets);

        return new JsonResponse($out);
    }

    // GET /api/trajets/{id} : détail
    #[Route('/api/trajets/{id}', name: 'api_trajet_detail', methods: ['GET'])]
    public function detail(Trajet $trajet): JsonResponse
    {
        return new JsonResponse([
            'id'            => $trajet->getId(),
            'villeDepart'   => $trajet->getVilleDepart(),
            'villeArrivee'  => $trajet->getVilleArrivee(),
            'dateDepart'    => $trajet->getDateDepart()->format('Y-m-d H:i'),
            'nbPlaces'      => $trajet->getNbPlaces(),
            'prix'          => $trajet->getPrix(),
            'ecologique'    => $trajet->isEcologique(),
            'actif'         => $trajet->isActif(),
            'chauffeur'     => [
                'id' => $trajet->getChauffeur()->getId(),
                'pseudo' => $trajet->getChauffeur()->getPseudo(),
            ],
        ]);
    }
}
