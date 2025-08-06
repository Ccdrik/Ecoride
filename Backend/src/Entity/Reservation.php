<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $passager = null;

    

    #[Groups(['reservation:read'])]
    #[ORM\Column]
    private ?int $nbPlacesReservees = 1;

    #[Groups(['reservation:read'])]
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trajet $trajet = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbPlacesReservees(): ?int
    {
        return $this->nbPlacesReservees;
    }

    public function setNbPlacesReservees(int $nbPlacesReservees): self
    {
        $this->nbPlacesReservees = $nbPlacesReservees;
        return $this;
    }

    // ✅ Ajouté pour compatibilité avec getPlacesDisponibles()
    public function getPlaces(): int
    {
        return $this->nbPlacesReservees ?? 0;
    }

    public function getPassager(): ?User
    {
        return $this->passager;
    }

    public function setPassager(User $passager): self
    {
        $this->passager = $passager;
        return $this;
    }

    public function getTrajet(): ?Trajet
    {
        return $this->trajet;
    }

    public function setTrajet(Trajet $trajet): self
    {
        $this->trajet = $trajet;
        return $this;
    }
}
