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
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    #[Route('/api/signup', name: 'api_signup', methods: ['POST'])]
    public function signup(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $JWTManager
    ): JsonResponse {
        // Récupération du JSON
        $data = json_decode($request->getContent(), true) ?? [];

        // Champs attendus depuis le front
        $nom     = trim($data['nom']     ?? '');
        $prenom  = trim($data['prenom']  ?? '');
        $pseudo  = trim($data['pseudo']  ?? '');
        $email   = trim($data['email']   ?? '');
        $password= (string)($data['password'] ?? '');
        $roles   = $data['roles'] ?? [];

        // Validation basique (coté contrôleur)
        if ($nom === '' || $prenom === '' || $pseudo === '' || $email === '' || $password === '' || empty($roles) || !is_array($roles)) {
            return new JsonResponse(['error' => 'Tous les champs sont requis'], 400);
        }

        // Normalisation des rôles (on garde uniquement ceux attendus)
        $roles = array_values(array_intersect($roles, ['ROLE_PASSAGER','ROLE_CHAUFFEUR','ROLE_EMPLOYEE','ROLE_ADMIN']));
        if (empty($roles)) {
            return new JsonResponse(['error' => 'Rôles invalides'], 400);
        }

        // Unicité email / pseudo
        if ($em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], 400);
        }
        if ($em->getRepository(User::class)->findOneBy(['pseudo' => $pseudo])) {
            return new JsonResponse(['error' => 'Pseudo déjà utilisé'], 400);
        }

        // Création utilisateur
        $user = new User();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setPseudo($pseudo);
        $user->setEmail($email);
        $user->setRoles($roles);

        // On positionne d’abord le mot de passe en clair pour que d’éventuelles contraintes @Assert\Length s’appliquent,
        // puis on le remplace par le hash après la validation métier.
        $user->setPassword($password);

        // Validation (annotations de l’entité)
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $messages], 400);
        }

        // Hash du mot de passe
        $user->setPassword($hasher->hashPassword($user, $password));

        // (Optionnel) crédits initiaux si ton entité les gère
        // if (method_exists($user, 'setCredits')) { $user->setCredits(20); }

        $em->persist($user);
        $em->flush();

        // Génération d’un token pour enchaîner côté front sans repasser par /signin (ton front gère aussi le cas sans token)
        $token = $JWTManager->create($user);

        return new JsonResponse([
            'message' => 'Utilisateur créé',
            'id'      => $user->getId(),
            'token'   => $token,
        ], 201);
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
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        return new JsonResponse([
            'id'     => $user->getId(),
            'email'  => $user->getEmail(),
            'pseudo' => method_exists($user, 'getPseudo') ? $user->getPseudo() : null,
            'roles'  => $user->getRoles(),
            // 'credits' => method_exists($user, 'getCredits') ? $user->getCredits() : null,
        ]);
    }

    #[Route('/api/signin', name: 'api_signin', methods: ['POST'])]
    public function signin(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $JWTManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $email    = (string)($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$hasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Identifiants invalides'], 401);
        }

        $token = $JWTManager->create($user);

        return new JsonResponse(['token' => $token], 200);
    }
}
