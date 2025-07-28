<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ApiController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/api/trajets', name: 'api_trajets_list', methods: ['GET'])]
    public function listTrajets(Request $request): JsonResponse
    {
        $depart = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $date = $request->query->get('date');

        $qb = $this->em->getRepository(Trajet::class)->createQueryBuilder('t')
            ->where('t.actif = true');

        if ($depart) {
            $qb->andWhere('LOWER(t.villeDepart) LIKE :depart')
                ->setParameter('depart', '%' . strtolower($depart) . '%');
        }

        if ($arrivee) {
            $qb->andWhere('LOWER(t.villeArrivee) LIKE :arrivee')
                ->setParameter('arrivee', '%' . strtolower($arrivee) . '%');
        }

        if ($date) {
            try {
                $dateStart = new \DateTimeImmutable($date . ' 00:00:00');
                $dateEnd = new \DateTimeImmutable($date . ' 23:59:59');
                $qb->andWhere('t.dateDepart BETWEEN :start AND :end')
                    ->setParameter('start', $dateStart)
                    ->setParameter('end', $dateEnd);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Date invalide'], 400);
            }
        }

        $trajets = $qb->getQuery()->getResult();

        $data = array_map(fn($t) => [
            'id' => $t->getId(),
            'chauffeur' => [
                'id' => $t->getChauffeur()->getId(),
                'pseudo' => $t->getChauffeur()->getPseudo(), // plus RGPD que l'email
            ],
            'dateDepart' => $t->getDateDepart()->format('Y-m-d H:i:s'),
            'villeDepart' => $t->getVilleDepart(),
            'villeArrivee' => $t->getVilleArrivee(),
            'nbPlaces' => $t->getNbPlaces(),
            'ecologique' => $t->isEcologique(),
            'prix' => $t->getPrix(),
        ], $trajets);

        return new JsonResponse($data);
    }

    #[Route('/api/trajets', name: 'api_trajets_create', methods: ['POST'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function createTrajet(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (!$data) return new JsonResponse(['error' => 'Données invalides'], 400);

        try {
            $date = new \DateTimeImmutable($data['dateDepart'] ?? 'now');
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Date invalide'], 400);
        }

        if (empty($data['villeDepart']) || empty($data['villeArrivee'])) {
            return new JsonResponse(['error' => 'Ville de départ et d\'arrivée requises'], 400);
        }

        $nbPlaces = (int) ($data['nbPlaces'] ?? 0);
        if ($nbPlaces <= 0) {
            return new JsonResponse(['error' => 'Nombre de places invalide'], 400);
        }

        $trajet = (new Trajet())
            ->setChauffeur($user)
            ->setDateDepart($date)
            ->setVilleDepart($data['villeDepart'])
            ->setVilleArrivee($data['villeArrivee'])
            ->setNbPlaces($nbPlaces)
            ->setEcologique((bool) ($data['ecologique'] ?? false))
            ->setPrix((float) ($data['prix'] ?? 0));

        $this->em->persist($trajet);
        $this->em->flush();

        return new JsonResponse(['message' => 'Trajet créé', 'id' => $trajet->getId()], 201);
    }

    #[Route('/api/trajets/{id}', name: 'api_trajets_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function deleteTrajet(int $id): JsonResponse
    {
        $user = $this->getUser();
        $trajet = $this->em->getRepository(Trajet::class)->find($id);
        if (!$trajet) return new JsonResponse(['error' => 'Trajet introuvable'], 404);

        if ($trajet->getChauffeur()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $this->em->remove($trajet);
        $this->em->flush();

        return new JsonResponse(['message' => 'Trajet supprimé']);
    }

    #[Route('/api/trajets/{id}/start', name: 'api_trajet_start', methods: ['PATCH'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function startTrajet(Trajet $trajet): JsonResponse
    {
        $user = $this->getUser();
        if ($trajet->getChauffeur() !== $user) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        if ($trajet->getEtat() !== 'prévu') {
            return new JsonResponse(['error' => 'Trajet déjà démarré ou terminé'], 400);
        }

        $trajet->setEtat('en cours');
        $this->em->flush();

        return new JsonResponse(['message' => 'Trajet démarré']);
    }

    #[Route('/api/trajets/{id}/end', name: 'api_trajet_end', methods: ['PATCH'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function endTrajet(Trajet $trajet, MailerInterface $mailer): JsonResponse
    {
        $user = $this->getUser();
        if ($trajet->getChauffeur() !== $user) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        if ($trajet->getEtat() !== 'en cours') {
            return new JsonResponse(['error' => 'Trajet non démarré'], 400);
        }

        $trajet->setEtat('terminé');
        $this->em->flush();

        foreach ($trajet->getReservations() as $reservation) {
            $passager = $reservation->getPassager();
            try {
                $email = (new Email())
                    ->from('no-reply@ecoride.fr')
                    ->to($passager->getEmail())
                    ->subject("Merci de valider votre trajet")
                    ->text("Bonjour, merci de valider votre trajet sur EcoRide.");
                $mailer->send($email);
            } catch (\Exception $e) {
                // On ignore l'erreur de mail pour ne pas bloquer l'API
            }
        }

        return new JsonResponse(['message' => 'Trajet terminé et notifications envoyées']);
    }

    #[Route('/api/trajets/{id}/finish', name: 'api_trajet_finish', methods: ['PATCH'])]
    #[IsGranted('ROLE_CHAUFFEUR')]
    public function finishTrajet(Trajet $trajet): JsonResponse
    {
        $user = $this->getUser();
        if ($trajet->getChauffeur() !== $user) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $trajet->setActif(false);
        $trajet->setEtat('terminé');
        $this->em->flush();

        return new JsonResponse(['message' => 'Trajet marqué comme terminé']);
    }
}
