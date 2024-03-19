<?php

namespace App\Entity\Game\Myrmes;

use App\Repository\Game\Myrmes\NurseMYRRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NurseMYRRepository::class)]
class NurseMYR
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'nurseMYRs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlayerMYR $player = null;

    #[ORM\Column]
    private ?bool $available = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getPlayer(): ?PlayerMYR
    {
        return $this->player;
    }

    public function setPlayer(?PlayerMYR $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): static
    {
        $this->available = $available;

        return $this;
    }
}
