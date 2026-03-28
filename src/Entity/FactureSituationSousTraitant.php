<?php

namespace App\Entity;

use App\Repository\FactureSituationSousTraitantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FactureSituationSousTraitantRepository::class)]
class FactureSituationSousTraitant
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\ManyToOne(inversedBy: 'sousTraitants')]
     #[ORM\JoinColumn(nullable: false)]
     #[Assert\NotNull(message: "La situation est obligatoire")]
     private ?FactureSituation $situation = null;

     #[ORM\ManyToOne]
     #[ORM\JoinColumn(nullable: false)]
     #[Assert\NotNull(message: "Le sous-traitant est obligatoire")]
     private ?SousTraitent $sousTraitant = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
     #[Assert\NotNull(message: "Le montant HT est obligatoire")]
     #[Assert\GreaterThanOrEqual(value: 0, message: "Le montant HT doit être supérieur ou égal à 0")]
     private ?string $montantHt = '0.00';

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     #[Assert\GreaterThanOrEqual(value: 0, message: "La TVA doit être supérieure ou égale à 0")]
     private ?string $tva = '0.00';

     #[ORM\Column(length: 255, nullable: true)]
     private ?string $reference = null;

     #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
     private ?\DateTimeInterface $dateContrat = null;

     #[ORM\Column(name: 'is_declared')]
     private ?bool $declarationSurContrat = false;

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getSituation(): ?FactureSituation
     {
          return $this->situation;
     }

     public function setSituation(?FactureSituation $situation): static
     {
          $this->situation = $situation;
          return $this;
     }

     public function getSousTraitant(): ?SousTraitent
     {
          return $this->sousTraitant;
     }

     public function setSousTraitant(?SousTraitent $sousTraitant): static
     {
          $this->sousTraitant = $sousTraitant;
          return $this;
     }

     public function getMontantHt(): ?string
     {
          return $this->montantHt;
     }

     public function setMontantHt(string $montantHt): static
     {
          $this->montantHt = $montantHt;
          return $this;
     }

     public function getTva(): ?string
     {
          return $this->tva;
     }

     public function setTva(?string $tva): static
     {
          $this->tva = $tva ? $tva : '0.00';
          return $this;
     }

     public function getReference(): ?string
     {
          return $this->reference;
     }

     public function setReference(?string $reference): static
     {
          $this->reference = $reference;
          return $this;
     }

     public function getDateContrat(): ?\DateTimeInterface
     {
          return $this->dateContrat;
     }

     public function setDateContrat(?\DateTimeInterface $dateContrat): static
     {
          $this->dateContrat = $dateContrat;
          return $this;
     }

     public function isDeclarationSurContrat(): ?bool
     {
          return $this->declarationSurContrat;
     }

     public function setDeclarationSurContrat(bool $declarationSurContrat): static
     {
          $this->declarationSurContrat = $declarationSurContrat;
          return $this;
     }
}
