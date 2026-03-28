<?php

namespace App\Entity;

use App\Repository\RappelFactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RappelFactureRepository::class)]
class RappelFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chantier $chantier = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateLimite = null;

    #[ORM\Column]
    private ?int $isPaid = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numFacture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\File(
        maxSize: "5M",
        mimeTypes: [
            "image/jpeg",
            "image/png",
            "image/tiff",
            "image/svg+xml",
            "application/pdf"
        ],
        mimeTypesMessage: "Veuillez télécharger une image (JPEG, PNG, TIFF, SVG) ou un fichier PDF valide."
    )]
    private ?string $file = null;

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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->dateLimite;
    }

    public function setDateLimite(\DateTimeInterface $dateLimite): static
    {
        $this->dateLimite = $dateLimite;

        return $this;
    }

    public function getIsPaid(): ?int
    {
        return $this->isPaid;
    }

    public function setIsPaid(int $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getNumFacture(): ?string
    {
        return $this->numFacture;
    }

    public function setNumFacture(?string $numFacture): static
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): static
    {
        $this->file = $file;

        return $this;
    }
}
