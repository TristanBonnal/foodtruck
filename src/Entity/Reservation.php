<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $reference;

    #[ORM\Column(type: 'datetime')]
    private $bookedAt;

    #[ORM\Column(type: 'integer')]
    private $spot;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getBookedAt(): ?\DateTimeInterface
    {
        return $this->bookedAt;
    }

    public function setBookedAt(\DateTimeInterface $bookedAt): self
    {
        $this->bookedAt = $bookedAt;

        return $this;
    }

    public function getSpot(): ?int
    {
        return $this->spot;
    }

    public function setSpot(int $spot): self
    {
        $this->spot = $spot;

        return $this;
    }

}
