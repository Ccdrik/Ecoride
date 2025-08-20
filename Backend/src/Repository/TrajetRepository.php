<?php

namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    /**
     * Recherche de trajets avec filtres.
     *
     * @param string|null                 $depart      Ville de départ (recherche partielle)
     * @param string|null                 $arrivee     Ville d'arrivée (recherche partielle)
     * @param \DateTimeImmutable|null     $date        Date (on matche le même jour)
     * @param bool|null                   $ecolo       true = électrique uniquement
     * @param float|int|string|null       $prixMax     prix <= prixMax
     * @param int|null                    $dureeMax    (placeholder si tu ajoutes un champ/colonne duree)
     * @param float|int|string|null       $noteMin     note min du chauffeur (si colonne "note" côté User)
     * @return Trajet[]
     */
    public function search(
        ?string $depart,
        ?string $arrivee,
        ?\DateTimeImmutable $date = null,
        ?bool $ecolo = null,
        $prixMax = null,
        ?int $dureeMax = null,
        $noteMin = null
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.actif = :actif')->setParameter('actif', true)
            ->andWhere('t.nbPlaces > 0')
            ->orderBy('t.dateDepart', 'ASC');

        // Ville de départ (LIKE insensible à la casse)
        if ($depart !== null && $depart !== '') {
            $qb->andWhere('LOWER(t.villeDepart) LIKE :depart')
               ->setParameter('depart', '%'.mb_strtolower($depart).'%');
        }

        // Ville d'arrivée
        if ($arrivee !== null && $arrivee !== '') {
            $qb->andWhere('LOWER(t.villeArrivee) LIKE :arrivee')
               ->setParameter('arrivee', '%'.mb_strtolower($arrivee).'%');
        }

        // Même jour que $date (00:00:00 → 23:59:59)
        if ($date instanceof \DateTimeImmutable) {
            $start = $date->setTime(0, 0, 0);
            $end   = $date->setTime(23, 59, 59);
            $qb->andWhere('t.dateDepart BETWEEN :start AND :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }

        // Écologique
        if ($ecolo !== null) {
            $qb->andWhere('t.ecologique = :ecolo')
               ->setParameter('ecolo', (bool)$ecolo);
        }

        // Prix max
        if ($prixMax !== null && $prixMax !== '') {
            $qb->andWhere('t.prix <= :prixMax')
               ->setParameter('prixMax', (float)$prixMax);
        }

        // Durée max (placeholder si tu ajoutes une colonne t.duree en minutes)
        if ($dureeMax !== null && $dureeMax > 0) {
            // $qb->andWhere('t.duree <= :dureeMax')
            //    ->setParameter('dureeMax', (int)$dureeMax);
        }

        // Note min chauffeur (si ta table User a une colonne "note")
        if ($noteMin !== null && $noteMin !== '') {
            // ⚠️ Nécessite que la relation ManyToOne Trajet->chauffeur existe
            // et que l'entité User ait une propriété/colonne "note" (float).
            $qb->leftJoin('t.chauffeur', 'u')
               ->addSelect('u')
               ->andWhere('u.note >= :noteMin')
               ->setParameter('noteMin', (float)$noteMin);
        }

        return $qb->getQuery()->getResult();
    }
}
