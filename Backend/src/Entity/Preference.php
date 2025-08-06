<?php

namespace App\Entity;

use App\Repository\PreferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: PreferenceRepository::class)]
class Preference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean')]
    private bool $fumeur = false;

    #[ORM\Column(type: 'boolean')]
    private bool $animaux = false;

    #[ORM\Column(type: 'boolean')]
    private bool $musique = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $autres = null;

    #[ORM\OneToOne(inversedBy: 'preference', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isFumeur(): bool
    {
        return $this->fumeur;
    }

    public function setFumeur(bool $fumeur): self
    {
        $this->fumeur = $fumeur;
        return $this;
    }

    public function isAnimaux(): bool
    {
        return $this->animaux;
    }

    public function setAnimaux(bool $animaux): self
    {
        $this->animaux = $animaux;
        return $this;
    }

    public function isMusique(): bool
    {
        return $this->musique;
    }

    public function setMusique(bool $musique): self
    {
        $this->musique = $musique;
        return $this;
    }

    public function getAutres(): ?string
    {
        return $this->autres;
    }

    public function setAutres(?string $autres): self
    {
        $this->autres = $autres;
        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
}
