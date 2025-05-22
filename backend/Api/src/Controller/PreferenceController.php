<?php

namespace App\Controller;

use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PreferenceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/api/preferences', name: 'api_preferences_save', methods: ['POST'])]
    public function savePreferences(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non connecté'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Aucune donnée reçue'], 400);
        }

        // ⚠️ Vérifie si une préférence existe déjà pour cet utilisateur
        $preference = $this->em->getRepository(Preference::class)->findOneBy(['utilisateur' => $user]);
        if (!$preference) {
            $preference = new Preference();
            $preference->setUtilisateur($user);
        }

        // ✅ Mise à jour des données
        $preference->setFumeur((bool)($data['fumeur'] ?? false));
        $preference->setAnimaux((bool)($data['animaux'] ?? false));
        $preference->setMusique((bool)($data['musique'] ?? false));
        $preference->setAutres($data['autres'] ?? '');

        $this->em->persist($preference);
        $this->em->flush();

        return new JsonResponse(['success' => 'Préférences enregistrées avec succès']);
    }
}
