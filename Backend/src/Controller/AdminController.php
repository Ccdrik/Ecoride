<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/api/admin/users/{id}/suspend', name: 'admin_suspend_user', methods: ['PATCH'])]
    public function suspendUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $user->setActif(false);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur suspendu']);
    }

    #[Route('/api/admin/users/{id}/activate', name: 'admin_activate_user', methods: ['PATCH'])]
    public function activateUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $user->setActif(true);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur activé']);
    }
}
