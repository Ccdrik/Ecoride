<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/api/check-email/{email}', name: 'check_email', methods: ['GET'])]
    public function checkEmail(string $email, EntityManagerInterface $em): JsonResponse
    {
        $exists = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        return new JsonResponse(['exists' => (bool) $exists]);
    }

    #[Route('/api/users', name: 'list_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listUsers(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();

        $data = array_map(fn(User $u) => [
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            'roles' => $u->getRoles()
        ], $users);

        return new JsonResponse($data);
    }
}
