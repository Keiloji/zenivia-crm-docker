<?php

namespace App\Entity;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['slot:read'])] // <-- Pour l'API
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['slot:read'])] // <-- Pour l'API
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'assignedTo')]
    private Collection $assignedTickets;

    /**
     * @var Collection<int, TicketComment>
     */
    #[ORM\OneToMany(targetEntity: TicketComment::class, mappedBy: 'author')]
    private Collection $ticketComments;

    /**
     * @var Collection<int, AvailabilitySlot>
     */
    #[ORM\OneToMany(targetEntity: AvailabilitySlot::class, mappedBy: 'technician', orphanRemoval: true)]
    private Collection $availabilitySlots;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'technician')]
    private Collection $appointments;

    public function __construct()
    {
        $this->assignedTickets = new ArrayCollection();
        $this->ticketComments = new ArrayCollection();
        $this->availabilitySlots = new ArrayCollection();
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getAssignedTickets(): Collection
    {
        return $this->assignedTickets;
    }

    public function addAssignedTicket(Ticket $assignedTicket): static
    {
        if (!$this->assignedTickets->contains($assignedTicket)) {
            $this->assignedTickets->add($assignedTicket);
            $assignedTicket->setAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedTicket(Ticket $assignedTicket): static
    {
        if ($this->assignedTickets->removeElement($assignedTicket)) {
            // set the owning side to null (unless already changed)
            if ($assignedTicket->getAssignedTo() === $this) {
                $assignedTicket->setAssignedTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TicketComment>
     */
    public function getTicketComments(): Collection
    {
        return $this->ticketComments;
    }

    public function addTicketComment(TicketComment $ticketComment): static
    {
        if (!$this->ticketComments->contains($ticketComment)) {
            $this->ticketComments->add($ticketComment);
            $ticketComment->setAuthor($this);
        }

        return $this;
    }

    public function removeTicketComment(TicketComment $ticketComment): static
    {
        if ($this->ticketComments->removeElement($ticketComment)) {
            // set the owning side to null (unless already changed)
            if ($ticketComment->getAuthor() === $this) {
                $ticketComment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AvailabilitySlot>
     */
    public function getAvailabilitySlots(): Collection
    {
        return $this->availabilitySlots;
    }

    public function addAvailabilitySlot(AvailabilitySlot $availabilitySlot): static
    {
        if (!$this->availabilitySlots->contains($availabilitySlot)) {
            $this->availabilitySlots->add($availabilitySlot);
            $availabilitySlot->setTechnician($this);
        }

        return $this;
    }

    public function removeAvailabilitySlot(AvailabilitySlot $availabilitySlot): static
    {
        if ($this->availabilitySlots->removeElement($availabilitySlot)) {
            // set the owning side to null (unless already changed)
            if ($availabilitySlot->getTechnician() === $this) {
                $availabilitySlot->setTechnician(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setTechnician($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getTechnician() === $this) {
                $appointment->setTechnician(null);
            }
        }

        return $this;
    }

    // --- ESSENTIEL POUR EASYADMIN ---
    public function __toString(): string
    {
        return $this->email ?? 'Utilisateur';
    }
}
