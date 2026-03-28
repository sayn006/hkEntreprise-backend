<?php

namespace App\Entity;

use App\Repository\ChantierSousTraitentRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ChantierSousTraitentRepository::class)]
#[ORM\Table(name: 'chantier_sous_traitent')]
class ChantierSousTraitent
{
     #[ORM\Id]
     #[ORM\ManyToOne(inversedBy: 'chantierSousTraitents')]
     #[ORM\JoinColumn(name: 'chantier_id', referencedColumnName: 'id', nullable: false)]
     private ?Chantier $chantier = null;

     #[ORM\Id]
     #[ORM\ManyToOne(inversedBy: 'chantierSousTraitents')]
     #[ORM\JoinColumn(name: 'sous_traitent_id', referencedColumnName: 'id', nullable: false)]
     private ?SousTraitent $sousTraitent = null;

     // Informations financières
     #[ORM\Column(length: 255, nullable: true)]
     private ?string $montantHt = null;

     #[ORM\Column(length: 255, nullable: true)]
     private ?string $tva = null;

     #[ORM\Column(length: 255, nullable: true)]
     private ?string $reference = null;

     #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
     private ?\DateTimeInterface $dateContrat = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     private ?string $description = null;

     #[ORM\Column(nullable: true)]
     private ?\DateTimeImmutable $createdAt = null;

     #[ORM\Column(nullable: true)]
     private ?\DateTimeImmutable $updatedAt = null;

     public function __construct()
     {
          $this->createdAt = new \DateTimeImmutable();
     }

     public function getChantier(): ?Chantier
     {
          return $this->chantier;
     }

     public function setChantier(?Chantier $chantier): self
     {
          $this->chantier = $chantier;
          return $this;
     }

     public function getSousTraitent(): ?SousTraitent
     {
          return $this->sousTraitent;
     }

     public function setSousTraitent(?SousTraitent $sousTraitent): self
     {
          $this->sousTraitent = $sousTraitent;
          return $this;
     }

     public function getMontantHt(): ?string
     {
          return $this->montantHt;
     }

     public function setMontantHt(?string $montantHt): self
     {
          $this->montantHt = $montantHt;
          return $this;
     }

     public function getTva(): ?string
     {
          return $this->tva;
     }

     public function setTva(?string $tva): self
     {
          $this->tva = $tva;
          return $this;
     }

     public function getReference(): ?string
     {
          return $this->reference;
     }

     public function setReference(?string $reference): self
     {
          $this->reference = $reference;
          return $this;
     }

     public function getDateContrat(): ?\DateTimeInterface
     {
          return $this->dateContrat;
     }

     public function setDateContrat(?\DateTimeInterface $dateContrat): self
     {
          $this->dateContrat = $dateContrat;
          return $this;
     }

     public function getDescription(): ?string
     {
          return $this->description;
     }

     public function setDescription(?string $description): self
     {
          $this->description = $description;
          return $this;
     }

     public function getCreatedAt(): ?\DateTimeImmutable
     {
          return $this->createdAt;
     }

     public function setCreatedAt(?\DateTimeImmutable $createdAt): self
     {
          $this->createdAt = $createdAt;
          return $this;
     }

     public function getUpdatedAt(): ?\DateTimeImmutable
     {
          return $this->updatedAt;
     }

     public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
     {
          $this->updatedAt = $updatedAt;
          return $this;
     }
}
