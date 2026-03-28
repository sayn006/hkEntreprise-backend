<?php

namespace App\Entity;

use App\Repository\FactureSituationPaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureSituationPaiementRepository::class)]
class FactureSituationPaiement
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\ManyToOne(inversedBy: 'paiements')]
     #[ORM\JoinColumn(nullable: false)]
     private ?FactureSituation $situation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
     private ?string $montant = null;

     #[ORM\Column(length: 255)]
     private ?string $status = 'En attente';

     #[ORM\Column(type: Types::DATE_MUTABLE)]
     private ?\DateTimeInterface $date = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     private ?string $commentaire = null;

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getSituation(): ?FactureSituation
     {
          return $this->situation;
     }

     public function setSituation(?FactureSituation $situation): self
     {
          $this->situation = $situation;
          return $this;
     }

     public function getMontant(): ?string
     {
          return $this->montant;
     }

     public function setMontant(string $montant): self
     {
          $this->montant = $montant;
          return $this;
     }

     public function getStatus(): ?string
     {
          return $this->status;
     }

     public function setStatus(string $status): self
     {
          $this->status = $status;
          return $this;
     }

     public function getDate(): ?\DateTimeInterface
     {
          return $this->date;
     }

     public function setDate(\DateTimeInterface $date): self
     {
          $this->date = $date;
          return $this;
     }

     public function getCommentaire(): ?string
     {
          return $this->commentaire;
     }

     public function setCommentaire(?string $commentaire): self
     {
          $this->commentaire = $commentaire;
          return $this;
     }
}
