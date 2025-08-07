<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est requis.")]
    #[Assert\Regex(
    pattern: "/^[a-zA-ZÀ-ÿ -]+$/",
    message: "Le nom ne doit contenir que des lettres et des traits d'union."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est requis.")]
    #[Assert\Regex(
    pattern: "/^[a-zA-ZÀ-ÿ -]+$/",
    message: "Le prénom ne doit contenir que des lettres et des traits d'union."
    )]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(
    message: "L'adresse email '{{ value }}' n'est pas valide.",
    mode: "html5"
    )]
    #[Assert\Regex(
    pattern: "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/",
    message: "Format d'email invalide (exemple : nom@domaine.com)."
    )]
    private ?string $email = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le pseudo est requis.")]
    #[Assert\Length(min: 3, max: 20, minMessage: "Le pseudo doit contenir au moins 3 caractères.")]
    #[Assert\Regex(
    pattern: "/^[a-zA-Z0-9_.-]+$/",
    message: "Le pseudo ne doit contenir que des lettres, chiffres, tirets, points ou underscores."
    )]
    private ?string $pseudo = null;


    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    #[Assert\Regex(
    pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/",
    message: "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial."
    )]
    private ?string $password = null;

    #[ORM\Column(type: "integer")]
    private int $credits = 20;

    #[ORM\Column(type: 'boolean')]
    private bool $actif = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $note = null;

    // Relation OneToMany vers Trajet avec mappedBy 'chauffeur'
    #[ORM\OneToMany(mappedBy: 'chauffeur', targetEntity: Trajet::class, orphanRemoval: true)]
    private Collection $trajets;

    // Relation OneToMany vers Reservation avec mappedBy 'user'
    #[ORM\OneToMany(mappedBy: 'passager', targetEntity: Reservation::class, orphanRemoval: true)]


    public function __construct()
    {
        $this->trajets = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->roles = [];
        $this->credits = 20;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    /**
     * @param array<int, string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): self
    {
        $this->credits = $credits;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function eraseCredentials(): void
    {
        // Pas de données sensibles temporaires à effacer ici
    }

    /** @return Collection<int, Trajet> */
    public function getTrajets(): Collection
    {
        return $this->trajets;
    }

    public function addTrajet(Trajet $trajet): self
    {
        if (!$this->trajets->contains($trajet)) {
            $this->trajets[] = $trajet;
            $trajet->setChauffeur($this);
        }

        return $this;
    }

    public function removeTrajet(Trajet $trajet): self
    {
        if ($this->trajets->removeElement($trajet)) {
            // set the owning side to null (unless already changed)
            if ($trajet->getChauffeur() === $this) {
                $trajet->setChauffeur(null);
            }
        }

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
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
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


    public function getPhoto(): ?string
    {
    return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
    $this->photo = $photo;
    return $this;
    }

    public function getNote(): ?float
    {
    return $this->note;
    }

public function setNote(?float $note): self
    {
    $this->note = $note;
    return $this;
    }
}
