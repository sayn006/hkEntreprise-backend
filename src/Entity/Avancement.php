<?php

namespace App\Entity;

use App\Repository\AvancementRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['avancement:read']],
    denormalizationContext: ['groups' => ['avancement:write']],
)]
#[ORM\Entity(repositoryClass: AvancementRepository::class)]
class Avancement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['avancement:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?Chantier $chantier = null;

    #[ORM\Column(length: 255)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?string $designation = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?string $unite = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?string $qte = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?float $pUnitaire = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?float $pTotal = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?string $m1 = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?string $p = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['avancement:read', 'avancement:write'])]
    private ?float $totalHt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    public function getQte(): ?string
    {
        return $this->qte;
    }

    public function setQte(?string $qte): static
    {
        $this->qte = $qte;

        return $this;
    }

    public function getPUnitaire(): ?float
    {
        return $this->pUnitaire;
    }

    public function setPUnitaire(?float $pUnitaire): static
    {
        $this->pUnitaire = $pUnitaire;

        return $this;
    }

    public function getPTotal(): ?float
    {
        return $this->pTotal;
    }

    public function setPTotal(?float $pTotal): static
    {
        $this->pTotal = $pTotal;

        return $this;
    }

    public function getM1(): ?string
    {
        return $this->m1;
    }

    public function setM1(?string $m1): static
    {
        $this->m1 = $m1;

        return $this;
    }

    public function getP(): ?string
    {
        return $this->p;
    }

    public function setP(?string $p): static
    {
        $this->p = $p;

        return $this;
    }

    public function getTotalHt(): ?float
    {
        return $this->totalHt;
    }

    public function setTotalHt(?float $totalHt): static
    {
        $this->totalHt = $totalHt;

        return $this;
    }
}
