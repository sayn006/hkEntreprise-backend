<?php

namespace App\Entity;

use App\Repository\FactureSituationTotalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureSituationTotalRepository::class)]
class FactureSituationTotal
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     private ?string $divers = null;

     #[ORM\Column(length: 100)]
     private ?string $designation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $montant = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $cumule = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $cumuleAnterieur = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $facturationFinDuMois = null;

     #[ORM\ManyToOne(inversedBy: 'totaux')]
     #[ORM\JoinColumn(nullable: false)]
     private ?FactureSituation $situation = null;

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getDivers(): ?string
     {
          return $this->divers;
     }

     public function setDivers(?string $divers): self
     {
          $this->divers = $divers;
          return $this;
     }

     public function getDesignation(): ?string
     {
          return $this->designation;
     }

     public function setDesignation(string $designation): self
     {
          $this->designation = $designation;
          return $this;
     }

     public function getMontant(): ?string
     {
          return $this->montant;
     }

     public function setMontant(?string $montant): self
     {
          $this->montant = $montant;
          return $this;
     }

     public function getCumule(): ?string
     {
          return $this->cumule;
     }

     public function setCumule(?string $cumule): self
     {
          $this->cumule = $cumule;
          return $this;
     }

     public function getCumuleAnterieur(): ?string
     {
          return $this->cumuleAnterieur;
     }

     public function setCumuleAnterieur(?string $cumuleAnterieur): self
     {
          $this->cumuleAnterieur = $cumuleAnterieur;
          return $this;
     }

     public function getFacturationFinDuMois(): ?string
     {
          return $this->facturationFinDuMois;
     }

     public function setFacturationFinDuMois(?string $facturationFinDuMois): self
     {
          $this->facturationFinDuMois = $facturationFinDuMois;
          return $this;
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
}
