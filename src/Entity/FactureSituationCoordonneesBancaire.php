<?php

namespace App\Entity;

use App\Repository\FactureSituationCoordonneesBancaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureSituationCoordonneesBancaireRepository::class)]
class FactureSituationCoordonneesBancaire
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     private ?string $divers = null;

     #[ORM\Column(length: 50)]
     private ?string $iban = null;

     #[ORM\Column(length: 50)]
     private ?string $banque = null;

     #[ORM\OneToOne]
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

     public function getIban(): ?string
     {
          return $this->iban;
     }

     public function setIban(string $iban): self
     {
          $this->iban = $iban;
          return $this;
     }

     public function getBanque(): ?string
     {
          return $this->banque;
     }

     public function setBanque(string $banque): self
     {
          $this->banque = $banque;
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
