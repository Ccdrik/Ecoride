<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
            'email' => $t->getChauffeur()->getEmail(),
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
    public function createTrajet(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non authentifié'], 401);

        $data = json_decode($request->getContent(), true);
        if (!$data) return new JsonResponse(['error' => 'Données invalides'], 400);

        try {
            $trajet = (new Trajet())
                ->setChauffeur($user)
                ->setDateDepart(new \DateTimeImmutable($data['dateDepart'] ?? 'now'))
                ->setVilleDepart($data['villeDepart'] ?? '')
                ->setVilleArrivee($data['villeArrivee'] ?? '')
                ->setNbPlaces($data['nbPlaces'] ?? 0)
                ->setEcologique($data['ecologique'] ?? false)
                ->setPrix($data['prix'] ?? 0);

            $this->em->persist($trajet);
            $this->em->flush();

            return new JsonResponse(['success' => 'Trajet créé', 'id' => $trajet->getId()], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur serveur : '.$e->getMessage()], 500);
        }
    }

    #[Route('/api/mes-trajets', name: 'api_mes_trajets', methods: ['GET'])]
    public function getMesTrajets(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);

        $trajets = $this->em->getRepository(Trajet::class)->findBy(['chauffeur' => $user]);

        $data = array_map(fn($t) => [
            'id' => $t->getId(),
            'depart' => $t->getVilleDepart(),
            'arrivee' => $t->getVilleArrivee(),
            'date_depart' => $t->getDateDepart()->format('Y-m-d'),
            'heure_depart' => $t->getDateDepart()->format('H:i'),
            'nb_places' => $t->getNbPlaces(),
            'prix' => $t->getPrix(),
                    ], $trajets);

        return new JsonResponse($data);
    }

    #[Route('/api/trajets/{id}', name: 'api_trajets_delete', methods: ['DELETE'])]
    public function deleteTrajet(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non authentifié'], 401);

        $trajet = $this->em->getRepository(Trajet::class)->find($id);
        if (!$trajet) return new JsonResponse(['error' => 'Trajet introuvable'], 404);

        if ($trajet->getChauffeur()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $this->em->remove($trajet);
        $this->em->flush();

        return new JsonResponse(['success' => 'Trajet supprimé']);
    }

    #[Route('/api/trajets/{id}/start', name: 'api_trajet_start', methods: ['PATCH'])]
#[IsGranted('ROLE_CHAUFFEUR')]
public function startTrajet(Trajet $trajet, EntityManagerInterface $em): JsonResponse
{
    $user = $this->getUser();
    if ($trajet->getChauffeur() !== $user) {
        return new JsonResponse(['message' => 'Non autorisé'], 403);
    }

    if ($trajet->getEtat() !== 'prévu') {
        return new JsonResponse(['message' => 'Trajet déjà démarré ou terminé'], 400);
    }

    $trajet->setEtat('en cours');
    $em->flush();

    return new JsonResponse(['message' => 'Trajet démarré']);
}

#[Route('/api/trajets/{id}/end', name: 'api_trajet_end', methods: ['PATCH'])]
#[IsGranted('ROLE_CHAUFFEUR')]
public function endTrajet(Trajet $trajet, EntityManagerInterface $em, MailerInterface $mailer): JsonResponse
{
    $user = $this->getUser();
    if ($trajet->getChauffeur() !== $user) {
        return new JsonResponse(['message' => 'Non autorisé'], 403);
    }

    if ($trajet->getEtat() !== 'en cours') {
        return new JsonResponse(['message' => 'Trajet non démarré'], 400);
    }

    $trajet->setEtat('terminé');
    $em->flush();

    // Notifier les passagers par email (si tu as le service Mailer)
    foreach ($trajet->getReservations() as $reservation) {
        $passager = $reservation->getPassager();
        $email = (new Email())
            ->from('no-reply@ecoride.fr')
            ->to($passager->getEmail())
            ->subject("Merci de valider votre trajet")
            ->text("Bonjour, merci de vous rendre sur votre espace EcoRide pour valider votre trajet.");
        $mailer->send($email);
    }

    return new JsonResponse(['message' => 'Trajet terminé et notifications envoyées']);
}

#[Route('/api/trajets/{id}/finish', name: 'api_trajet_finish', methods: ['PATCH'])]
#[IsGranted('ROLE_CHAUFFEUR')]
public function finishTrajet(Trajet $trajet): JsonResponse
{
    $user = $this->getUser();
    if ($trajet->getChauffeur() !== $user) {
        return new JsonResponse(['message' => 'Non autorisé'], 403);
    }

    $trajet->setActif(false);
    $this->em->flush();

    return new JsonResponse(['message' => 'Trajet marqué comme terminé']);
}


}
