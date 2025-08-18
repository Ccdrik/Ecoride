<?php

namespace App\Entity;

use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
#[ORM\Table(name: 'trajet', indexes: [
    new ORM\Index(name: 'idx_trajet_ville_depart', columns: ['ville_depart']),
    new ORM\Index(name: 'idx_trajet_ville_arrivee', columns: ['ville_arrivee']),
    new ORM\Index(name: 'idx_trajet_date_depart', columns: ['date_depart']),
])]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['trajet:read', 'reservation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, name: 'ville_depart')]
    #[Assert\NotBlank(message: 'La ville de départ est requise.')]
    #[Groups(['trajet:read', 'reservation:read'])]
    private ?string $villeDepart = null;

    #[ORM\Column(length: 255, name: 'ville_arrivee')]
    #[Assert\NotBlank(message: 'La ville d\'arrivée est requise.')]
    #[Groups(['trajet:read', 'reservation:read'])]
    private ?string $villeArrivee = null;

    #[ORM\Column(name: 'date_depart')]
    #[Assert\NotNull(message: 'La date de départ est requise.')]
    #[Groups(['trajet:read', 'reservation:read'])]
    private ?\DateTimeImmutable $dateDepart = null;

    #[ORM\Column(name: 'nb_places')]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le nombre de places doit être un entier positif.')]
    #[Groups(['trajet:read'])]
    private ?int $nbPlaces = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le prix doit être positif.')]
    #[Groups(['trajet:read'])]
    private ?float $prix = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['trajet:read'])]
    private bool $ecologique = false;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le chauffeur est requis.')]
    private ?User $chauffeur = null;

    #[ORM\OneToMany(mappedBy: 'trajet', targetEntity: Reservation::class, orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['trajet:read'])]
    private bool $actif = true;

    // --- Coordonnées GPS (nullable) ---
    #[ORM\Column(type: 'float', nullable: true, name: 'depart_lat')]
    #[Groups(['trajet:read'])]
    private ?float $departLat = null;

    #[ORM\Column(type: 'float', nullable: true, name: 'depart_lng')]
    #[Groups(['trajet:read'])]
    private ?float $departLng = null;

    #[ORM\Column(type: 'float', nullable: true, name: 'arrivee_lat')]
    #[Groups(['trajet:read'])]
    private ?float $arriveeLat = null;

    #[ORM\Column(type: 'float', nullable: true, name: 'arrivee_lng')]
    #[Groups(['trajet:read'])]
    private ?float $arriveeLng = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    // -------- Getters / Setters --------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVilleDepart(): ?string
    {
        return $this->villeDepart;
    }

    public function setVilleDepart(string $villeDepart): self
    {
        $this->villeDepart = $villeDepart;
        return $this;
    }

    public function getVilleArrivee(): ?string
    {
        return $this->villeArrivee;
    }

    public function setVilleArrivee(string $villeArrivee): self
    {
        $this->villeArrivee = $villeArrivee;
        return $this;
    }

    public function getDateDepart(): ?\DateTimeImmutable
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeImmutable $dateDepart): self
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nbPlaces;
    }

    public function setNbPlaces(int $nbPlaces): self
    {
        $this->nbPlaces = $nbPlaces;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function isEcologique(): bool
    {
        return $this->ecologique;
    }

    public function setEcologique(bool $ecologique): self
    {
        $this->ecologique = $ecologique;
        return $this;
    }

    public function getChauffeur(): ?User
    {
        return $this->chauffeur;
    }

    public function setChauffeur(User $chauffeur): self
    {
        $this->chauffeur = $chauffeur;
        return $this;
    }

    /** @return Collection<int, Reservation> */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setTrajet($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->reservations->removeElement($reservation);
        return $this;
    }

    /**
     * Places disponibles calculées à partir des réservations.
     */
    public function getPlacesDisponibles(): int
    {
        $placesReservees = array_reduce(
            $this->reservations->toArray(),
            fn(int $total, $reservation) => $total + (int) $reservation->getPlaces(),
            0
        );

        return max(0, (int)$this->nbPlaces - $placesReservees);
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    public function getDepartLat(): ?float { return $this->departLat; }
public function setDepartLat(?float $departLat): self { $this->departLat = $departLat; return $this; }

public function getDepartLng(): ?float { return $this->departLng; }
public function setDepartLng(?float $departLng): self { $this->departLng = $departLng; return $this; }

public function getArriveeLat(): ?float { return $this->arriveeLat; }
public function setArriveeLat(?float $arriveeLat): self { $this->arriveeLat = $arriveeLat; return $this; }

public function getArriveeLng(): ?float { return $this->arriveeLng; }
public function setArriveeLng(?float $arriveeLng): self { $this->arriveeLng = $arriveeLng; return $this; }

}
