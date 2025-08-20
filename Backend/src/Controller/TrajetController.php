<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Voiture;
use App\Repository\TrajetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TrajetController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    // POST /api/trajets : créer un trajet (protégé)
    #[IsGranted('ROLE_CHAUFFEUR')]
    #[Route('/api/trajets', name: 'api_trajet_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // 🔐 Sécurité : s'assurer qu'on a bien un user
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentification requise'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        foreach (['villeDepart','villeArrivee','dateDepart','nbPlaces','prix'] as $k) {
            if (!isset($data[$k]) || $data[$k] === '') {
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

        // 🚗 (optionnel) Associer une voiture existante via voitureId
        if (!empty($data['voitureId'])) {
            $voiture = $this->em->getRepository(Voiture::class)->find((int) $data['voitureId']);
            if (!$voiture) {
                return new JsonResponse(['error' => 'Voiture introuvable'], 404);
            }
            $trajet->setVoiture($voiture);
            // (optionnel) hériter des places :
            // $trajet->setNbPlaces($voiture->getPlaces());
        }

        $this->em->persist($trajet);
        $this->em->flush();

        return new JsonResponse([
            'id'      => $trajet->getId(),
            'message' => 'Trajet créé',
        ], 201);
    }

    // GET /api/trajets?depart=...&arrivee=...&date=YYYY-MM-DD  (PUBLIC)
    #[Route('/api/trajets', name: 'api_trajet_list', methods: ['GET'])]
    public function list(Request $request, TrajetRepository $repo): JsonResponse
    {
        $depart   = $request->query->get('depart');
        $arrivee  = $request->query->get('arrivee');
        $date     = $request->query->get('date'); // optionnel

        // 🔎 Filtres supplémentaires
        $ecolo    = $request->query->get('ecolo');     // "1" ou "" (ou null)
        $prixMax  = $request->query->get('prixMax');
        $dureeMax = $request->query->get('dureeMax');
        $noteMin  = $request->query->get('noteMin');

        $dateObj = null;
        if ($date) {
            try { $dateObj = new \DateTimeImmutable($date.' 00:00:00'); } catch (\Exception $e) {}
        }

        // cast propre pour écolo
        $ecoloBool = null;
        if ($ecolo !== null && $ecolo !== '') {
            $ecoloBool = (bool) ((int) $ecolo); // "1" => true, "0" => false
        }

        $trajets = $repo->search(
            $depart,
            $arrivee,
            $dateObj,
            $ecoloBool,
            $prixMax,
            $dureeMax ? (int)$dureeMax : null,
            $noteMin
        );

        $out = array_map(fn(Trajet $t) => [
            'id'            => $t->getId(),
            'villeDepart'   => $t->getVilleDepart(),
            'villeArrivee'  => $t->getVilleArrivee(),
            'dateDepart'    => $t->getDateDepart()->format('Y-m-d H:i'),
            'nbPlaces'      => $t->getNbPlaces(),
            'prix'          => $t->getPrix(),
            'ecologique'    => $t->isEcologique(),
            'actif'         => $t->isActif(),
            'voiture'       => $t->getVoiture() ? [
                'marque' => $t->getVoiture()->getMarque(),
                'modele' => $t->getVoiture()->getModele(),
            ] : null,
            'chauffeur'     => [
                'id'     => $t->getChauffeur()->getId(),
                'pseudo' => $t->getChauffeur()->getPseudo(),
            ],
        ], $trajets);

        return new JsonResponse($out);
    }

    // GET /api/trajets/{id} : détail  (PUBLIC)
    #[Route('/api/trajets/{id}', name: 'api_trajet_detail', methods: ['GET'])]
    public function detail(Trajet $trajet): JsonResponse
    {
        $voiture = $trajet->getVoiture();

        return new JsonResponse([
            'id'            => $trajet->getId(),
            'villeDepart'   => $trajet->getVilleDepart(),
            'villeArrivee'  => $trajet->getVilleArrivee(),
            'dateDepart'    => $trajet->getDateDepart()->format('Y-m-d H:i'),
            'nbPlaces'      => $trajet->getNbPlaces(),
            'prix'          => $trajet->getPrix(),
            'ecologique'    => $trajet->isEcologique(),
            'actif'         => $trajet->isActif(),
            'voiture'       => $voiture ? [
                'marque' => $voiture->getMarque(),
                'modele' => $voiture->getModele(),
            ] : null,
            'chauffeur'     => [
                'id'     => $trajet->getChauffeur()->getId(),
                'pseudo' => $trajet->getChauffeur()->getPseudo(),
            ],
        ]);
    }
}
