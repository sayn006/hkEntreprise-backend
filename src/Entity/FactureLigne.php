<?php

namespace App\Entity;

use App\Repository\FactureLigneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['facture:read']],
    denormalizationContext: ['groups' => ['facture:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['facture' => 'exact'])]
#[ORM\Entity(repositoryClass: FactureLigneRepository::class)]
class FactureLigne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['facture:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?string $designation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?string $prixUnitaireHt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '20.00'])]
    #[Groups(['facture:read', 'facture:write'])]
    private ?string $tauxTva = '20.00';

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['facture_ligne:read', 'facture_ligne:write'])]
    private ?Facture $facture = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaireHt(): ?string
    {
        return $this->prixUnitaireHt;
    }

    public function setPrixUnitaireHt(?string $prixUnitaireHt): static
    {
        $this->prixUnitaireHt = $prixUnitaireHt;
        return $this;
    }

    public function getTauxTva(): ?string
    {
        return $this->tauxTva;
    }

    public function setTauxTva(?string $tauxTva): static
    {
        $this->tauxTva = $tauxTva;
        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;
        return $this;
    }

    #[Groups(['facture:read'])]
    public function getMontantHt(): ?string
    {
        if ($this->quantite === null || $this->prixUnitaireHt === null) {
            return null;
        }
        return number_format($this->quantite * floatval($this->prixUnitaireHt), 2, '.', '');
    }

    #[Groups(['facture:read'])]
    public function getMontantTtc(): ?string
    {
        $montantHt = $this->getMontantHt();
        if ($montantHt === null) {
            return null;
        }
        $tva = $this->tauxTva ? floatval($this->tauxTva) : 20;
        return number_format(floatval($montantHt) * (1 + $tva / 100), 2, '.', '');
    }
}
