<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: "App\Repository\VoitureRepository")]
#[UniqueEntity(fields: ['immatriculation'], message: 'Cette immatriculation existe déjà.')]
class Voiture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:100)]
    private string $marque;

    #[ORM\Column(type:"string", length:100)]
    private string $modele;

    // 🔒 unicité en base
    #[ORM\Column(type:"string", length:20, unique:true)]
    private string $immatriculation;

    #[ORM\Column(type:"string", length:50, nullable:true)]
    private ?string $couleur = null;

    #[ORM\Column(type:"string", length:20)]
    private string $energie;

    #[ORM\Column(type:"date")]
    private \DateTimeInterface $premiereImmat;

    #[ORM\Column(type:"integer")]
    private int $places;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "voitures")]
    #[ORM\JoinColumn(nullable:false)]
    private User $proprietaire;

    // --- Getters / Setters ---
    public function getId(): ?int { return $this->id; }

    public function getMarque(): string { return $this->marque; }
    public function setMarque(string $marque): self { $this->marque = $marque; return $this; }

    public function getModele(): string { return $this->modele; }
    public function setModele(string $modele): self { $this->modele = $modele; return $this; }

    public function getImmatriculation(): string { return $this->immatriculation; }
    public function setImmatriculation(string $immatriculation): self { $this->immatriculation = $immatriculation; return $this; }

    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(?string $couleur): self { $this->couleur = $couleur; return $this; }

    public function getEnergie(): string { return $this->energie; }
    public function setEnergie(string $energie): self { $this->energie = $energie; return $this; }

    public function getPremiereImmat(): \DateTimeInterface { return $this->premiereImmat; }
    public function setPremiereImmat(\DateTimeInterface $premiereImmat): self { $this->premiereImmat = $premiereImmat; return $this; }

    public function getPlaces(): int { return $this->places; }
    public function setPlaces(int $places): self { $this->places = $places; return $this; }

    public function getProprietaire(): User { return $this->proprietaire; }
    public function setProprietaire(User $proprietaire): self { $this->proprietaire = $proprietaire; return $this; }
}
