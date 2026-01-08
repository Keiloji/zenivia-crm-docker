<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['ticket:read']]
)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ticket:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['ticket:read'])]
    // --- AJOUT VALIDATION ---
    #[Assert\NotBlank(message: "Le titre du ticket est obligatoire.")]
    #[Assert\Length(
        min: 5, 
        max: 255, 
        minMessage: "Le titre doit faire au moins {{ limit }} caractères."
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['ticket:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    #[Groups(['ticket:read'])]
    #[Assert\Choice(
        choices: ['Nouveau', 'En cours', 'Résolu'],
        message: "Le statut doit être 'Nouveau', 'En cours' ou 'Résolu'."
    )]
    private ?string $status = null;

    #[ORM\Column(length: 100)]
    #[Groups(['ticket:read'])]
    #[Assert\Choice(
        choices: ['Faible', 'Moyenne', 'Urgente'],
        message: "La priorité doit être 'Faible', 'Moyenne' ou 'Urgente'."
    )]
    private ?string $priority = null;

    #[ORM\Column]
    #[Groups(['ticket:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ticket:read'])]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'assignedTickets')]
    #[Groups(['ticket:read'])]
    private ?User $assignedTo = null;

    /**
     * @var Collection<int, TicketComment>
     */
    #[ORM\OneToMany(targetEntity: TicketComment::class, mappedBy: 'ticket', orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * @return Collection<int, TicketComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(TicketComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTicket($this);
        }

        return $this;
    }

    public function removeComment(TicketComment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            
            if ($comment->getTicket() === $this) {
                $comment->setTicket(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->title ?? 'Ticket';
    }
    #[ORM\PrePersist]
    public function prePersistSetCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
    #[Assert\Callback]
    public function validateStatusConsistency(ExecutionContextInterface $context): void
    {
        // Si le ticket est résolu MAIS qu'il n'y a pas de technicien assigné
        if ($this->getStatus() === 'Résolu' && $this->getAssignedTo() === null) {
            $context->buildViolation('Un ticket ne peut pas être résolu sans technicien assigné.')
                ->atPath('status')
                ->addViolation();
        }
    }
}
