<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\FactureSituationRetenueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['fs_retenue:read']],
    denormalizationContext: ['groups' => ['fs_retenue:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['situation' => 'exact'])]
#[ORM\Entity(repositoryClass: FactureSituationRetenueRepository::class)]
class FactureSituationRetenue
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     #[Groups(['fs_retenue:read'])]
     private ?int $id = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
     private ?string $divers = null;

     #[ORM\Column(length: 100)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
     private ?string $designation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
     private ?string $montant = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
     private ?string $cumule = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
     private ?string $cumuleAnterieur = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
     private ?string $facturationFinDuMois = null;

     #[ORM\ManyToOne(inversedBy: 'retenues')]
     #[ORM\JoinColumn(nullable: false)]
     #[Groups(['fs_retenue:read', 'fs_retenue:write'])]
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
