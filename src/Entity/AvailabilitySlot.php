<?php

namespace App\Entity;

use App\Repository\AvailabilitySlotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
// --- AJOUTER CET IMPORT ---
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AvailabilitySlotRepository::class)]
class AvailabilitySlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['slot:read'])] // <-- AJOUTER
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'availabilitySlots')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['slot:read'])] // <-- AJOUTER (pour voir le technicien)
    private ?User $technician = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['slot:read'])] // <-- AJOUTER (La date de début !)
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['slot:read'])] // <-- AJOUTER (La date de fin !)
    private ?\DateTimeImmutable $endTime = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isBooked = null;

    public function __construct()
    {
        $this->isBooked = false;
    }

    // ... (Le reste des méthodes getters/setters ne change pas)

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTechnician(): ?User
    {
        return $this->technician;
    }

    public function setTechnician(?User $technician): static
    {
        $this->technician = $technician;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function isBooked(): ?bool
    {
        return $this->isBooked;
    }

    public function setIsBooked(bool $isBooked): static
    {
        $this->isBooked = $isBooked;

        return $this;
    }

    public function __toString(): string
    {
        $tech = $this->technician ? (string) $this->technician : '—';
        $start = $this->startTime ? $this->startTime->format('d/m/Y H:i') : '?';
        $end = $this->endTime ? $this->endTime->format('d/m/Y H:i') : '?';

        return sprintf('%s — %s à %s', $tech, $start, $end);
    }
}
