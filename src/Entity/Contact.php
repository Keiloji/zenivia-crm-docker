<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    // AJOUT : Validation Prénom
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le prénom doit faire au moins {{ limit }} caractères.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    // AJOUT : Validation Nom
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Le nom doit faire au moins {{ limit }} caractères.")]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    // AJOUT : Validation Email
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    // AJOUT : Validation Téléphone (Facultatif mais limité)
    #[Assert\Length(max: 20, maxMessage: "Le numéro ne peut pas dépasser 20 caractères.")]
    private ?string $phone = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    // AJOUT : Validation Client (Doit être valide)
    #[Assert\NotNull(message: "Le contact doit être rattaché à un client.")]
    private ?Client $client = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    // --- Getters et Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // --- Logique d'auto-remplissage ---

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName . ' (' . $this->email . ')';
    }
}
