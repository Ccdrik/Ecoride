<?php

namespace App\Controller;

use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PreferenceController extends AbstractController
{
    #[Route('/api/preferences', name: 'update_preferences', methods: ['POST'])]
    public function updatePreferences(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $pref = $em->getRepository(Preference::class)->findOneBy(['utilisateur' => $user]) ?? new Preference();
        $pref->setUtilisateur($user);
        $pref->setFumeur((bool) ($data['fumeur'] ?? false));
        $pref->setAnimaux((bool) ($data['animaux'] ?? false));
        $pref->setMusique((bool) ($data['musique'] ?? false));

        $em->persist($pref);
        $em->flush();

        return new JsonResponse(['message' => 'Préférences mises à jour']);
    }
}
