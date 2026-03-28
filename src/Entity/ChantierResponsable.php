<?php

namespace App\Entity;

use App\Repository\ChantierResponsableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChantierResponsableRepository::class)]
class ChantierResponsable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chantierResponsables')]
    private ?User $responsable = null;

    #[ORM\ManyToOne(inversedBy: 'chantierResponsables')]
    private ?Chantier $chantier = null;

    #[ORM\Column(nullable: true)]
    private ?int $notification = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): static
    {
        $this->responsable = $responsable;
        return $this;
    }

    public function getChantier(): ?Chantier
    {
        return $this->chantier;
    }

    public function setChantier(?Chantier $chantier): static
    {
        $this->chantier = $chantier;
        return $this;
    }

    public function getNotification(): ?int
    {
        return $this->notification;
    }

    public function setNotification(?int $notification): static
    {
        $this->notification = $notification;
        return $this;
    }
}
