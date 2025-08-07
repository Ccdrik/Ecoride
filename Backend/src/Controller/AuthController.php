<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AuthController extends AbstractController
{
    #[Route('/api/signup', name: 'api_signup', methods: ['POST'])]
public function signup(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, ValidatorInterface $validator): JsonResponse

{
    $data = json_decode($request->getContent(), true);

    // Vérification des champs requis
    if (
        empty($data['nom']) ||
        empty($data['prenom']) ||
        empty($data['pseudo']) ||
        empty($data['email']) ||
        empty($data['password']) ||
        empty($data['confirmpassword']) ||
        empty($data['roles'])
    ) {
        return new JsonResponse(['error' => 'Tous les champs sont requis'], 400);
    }


    if ($data['password'] !== $data['confirmpassword']) {
        return new JsonResponse(['error' => 'Les mots de passe ne correspondent pas'], 400);
    }

    if ($em->getRepository(User::class)->findOneBy(['email' => $data['email']])) {
    return new JsonResponse(['error' => 'Email déjà utilisé'], 400);
}

if ($em->getRepository(User::class)->findOneBy(['pseudo' => $data['pseudo']])) {
    return new JsonResponse(['error' => 'Pseudo déjà utilisé'], 400);
}

    $user = new User();
    $user->setNom($data['nom']);
    $user->setPrenom($data['prenom']);
    $user->setPseudo($data['pseudo']);
    $user->setEmail($data['email']);
    $user->setRoles($data['roles']);

    $user->setPassword($data['password']);
    $errors = $validator->validate($user);
    if (count($errors) > 0) {
    $messages = [];
    foreach ($errors as $error) {
        $messages[] = $error->getMessage();
    }
    return new JsonResponse(['errors' => $messages], 400);
}
$user->setPassword($hasher->hashPassword($user, $data['password']));

    $em->persist($user);
    $em->flush();

    return new JsonResponse(['message' => 'Utilisateur créé', 'id' => $user->getId()], 201);
}

    #[Route('/api/check-email/{email}', name: 'api_check_email', methods: ['GET'])]
    public function checkEmail(string $email, EntityManagerInterface $em): JsonResponse
    {
        $exists = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        return new JsonResponse(['exists' => (bool) $exists]);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return new JsonResponse(['error' => 'Non authentifié'], 401);

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]);
    }
}
