<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    #[Route('/api/signup', name: 'api_signup', methods: ['POST'])]
public function signup(
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $hasher,
    JWTTokenManagerInterface $jwtManager
): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!$data) {
        return new JsonResponse(['error' => 'Données manquantes ou format invalide'], 400);
    }

    $email = $data['email'] ?? null;
    $password = $data['motdepasse'] ?? null;
    $nom = $data['nom'] ?? null;
    $prenom = $data['prenom'] ?? null;
    $pseudo = $data['pseudo'] ?? null;
    $rolesFromRequest = $data['roles'] ?? [];

    if (!$email || !$password || !$nom || !$prenom || !$pseudo) {
        return new JsonResponse(['error' => 'Champs manquants'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return new JsonResponse(['error' => 'Email invalide'], 400);
    }

    $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
    if ($existingUser) {
        return new JsonResponse(['error' => 'Email déjà utilisé'], 400);
    }

    // 🔒 Protection : seuls les admins peuvent créer des comptes EMPLOYE ou ADMIN
    $rolesInterdits = ['ROLE_EMPLOYE', 'ROLE_ADMIN'];
    $isAdmin = $this->isGranted('ROLE_ADMIN');

    foreach ($rolesFromRequest as $r) {
        if (in_array($r, $rolesInterdits) && !$isAdmin) {
            return new JsonResponse(['error' => 'Seul un admin peut attribuer ce rôle'], 403);
        }
    }

    // ✅ Création du compte
    $user = new User();
    $user->setEmail($email);
    $user->setNom($nom);
    $user->setPrenom($prenom);
    $user->setPseudo($pseudo);
    $user->setCredits(20);

    $user->setPassword($hasher->hashPassword($user, $password));
    $user->setRoles(!empty($rolesFromRequest) ? $rolesFromRequest : ['ROLE_PASSAGER']);

    try {
        $em->persist($user);
        $em->flush();

        // 🔐 Retourne le token uniquement pour un utilisateur non connecté (création depuis le formulaire public)
        if (!$this->getUser()) {
            $token = $jwtManager->create($user);
            return new JsonResponse(['token' => $token, 'success' => true], 201);
        }

        return new JsonResponse(['success' => true, 'message' => 'Utilisateur créé avec succès'], 201);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Une erreur est survenue : ' . $e->getMessage()], 500);
    }
}


    #[Route('/api/check-email', name: 'api_check_email', methods: ['GET'])]
    public function checkEmail(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $email = $request->query->get('email');

        if (!$email) {
            return new JsonResponse(['error' => 'Email manquant'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        return new JsonResponse([
            'available' => $user === null
        ]);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function getMe(): JsonResponse
    {
    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['error' => 'Non authentifié'], 401);
    }

    return new JsonResponse([
        'id' => $user->getId(),
        'nom' => $user->getNom(),
        'pseudo' => $user->getPseudo(),
        'email' => $user->getEmail(),
        'roles' => $user->getRoles(),
        'credits' => $user->getCredits(),
    ]);


}


#[Route('/api/credits', name: 'api_credits_add', methods: ['POST'])]
public function ajouterCredits(Request $request, EntityManagerInterface $em): JsonResponse
{
    $user = $this->getUser();
    if (!$user) return new JsonResponse(['error' => 'Non connecté'], 401);

    $data = json_decode($request->getContent(), true);
    $ajout = $data['ajout'] ?? 0;

    $user->setCredits($user->getCredits() + (int)$ajout);
    $em->flush();

    return new JsonResponse(['success' => true, 'credits' => $user->getCredits()]);
}

}
