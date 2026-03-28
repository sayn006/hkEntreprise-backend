<?php

namespace App\Entity;

use App\Repository\FactureSituationFacturationTravauxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureSituationFacturationTravauxRepository::class)]
class FactureSituationFacturationTravaux
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\Column(length: 50, nullable: true)]
     private ?string $type = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     private ?string $divers = null;

     #[ORM\Column(length: 100, nullable: true)]
     private ?string $designation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $montant = '0.00';

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $cumule = '0.00';

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $cumuleAnterieur = '0.00';

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $facturationFinDuMois = '0.00';

     #[ORM\ManyToOne(inversedBy: 'facturationTravaux')]
     #[ORM\JoinColumn(nullable: false)]
     private ?FactureSituation $situation = null;

     public function __construct()
     {
          $this->montant = '0.00';
          $this->cumule = '0.00';
          $this->cumuleAnterieur = '0.00';
          $this->facturationFinDuMois = '0.00';
     }

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getType(): ?string
     {
          return $this->type;
     }

     public function setType(?string $type): self
     {
          $this->type = $type;
          return $this;
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
