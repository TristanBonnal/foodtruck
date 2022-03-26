<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['show_reservation'])]
    private $reference;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['show_reservation'])]
    private $bookedAt;

    #[ORM\Column(type: 'integer')]
    #[Groups(['show_reservation'])]
    #[Assert\Range(
        min: 1,
        max: 7
    )]
    private $spot;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservation')]
    #[ORM\JoinColumn(nullable: false)]
    // #[Groups(['show_reservation'])]
    private $user;

    public function __construct ()
    {
        $this->reference = (new \DateTime())->format('YmdH') . '-' . uniqid();
    }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
