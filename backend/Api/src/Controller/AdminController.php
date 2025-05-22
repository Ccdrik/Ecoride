<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ✅ Liste des utilisateurs (admin seulement)
    #[Route('/users', name: 'admin_users_list', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $users = $this->em->getRepository(User::class)->findAll();

        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            'pseudo' => $u->getPseudo(),
            'nom' => $u->getNom(),
            'prenom' => $u->getPrenom(),
            'roles' => $u->getRoles(),
            'credits' => $u->getCredits(),
            'actif' => $u->isActif(),
        ], $users);

        return new JsonResponse($data);
    }

    // ✅ Changer le statut actif/inactif d'un utilisateur (admin seulement)
    #[Route('/users/{id}/toggle', name: 'admin_user_toggle', methods: ['PUT'])]
    public function toggleUser(int $id): JsonResponse
    {
        $admin = $this->getUser();
        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles())) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        $user->setActif(!$user->isActif());
        $this->em->flush();

        return new JsonResponse(['success' => 'Statut mis à jour', 'actif' => $user->isActif()]);
    }
}
