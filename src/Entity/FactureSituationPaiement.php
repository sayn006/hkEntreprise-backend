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
use App\Repository\FactureSituationPaiementRepository;
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
    normalizationContext: ['groups' => ['fs_paiement:read']],
    denormalizationContext: ['groups' => ['fs_paiement:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['situation' => 'exact', 'status' => 'exact'])]
#[ORM\Entity(repositoryClass: FactureSituationPaiementRepository::class)]
class FactureSituationPaiement
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     #[Groups(['fs_paiement:read'])]
     private ?int $id = null;

     #[ORM\ManyToOne(inversedBy: 'paiements')]
     #[ORM\JoinColumn(nullable: false)]
     #[Groups(['fs_paiement:read', 'fs_paiement:write'])]
     private ?FactureSituation $situation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
     #[Groups(['fs_paiement:read', 'fs_paiement:write'])]
     private ?string $montant = null;

     #[ORM\Column(length: 255)]
     #[Groups(['fs_paiement:read', 'fs_paiement:write'])]
     private ?string $status = 'En attente';

     #[ORM\Column(type: Types::DATE_MUTABLE)]
     #[Groups(['fs_paiement:read', 'fs_paiement:write'])]
     private ?\DateTimeInterface $date = null;

     #[ORM\Column(type: Types::TEXT, nullable: true)]
     #[Groups(['fs_paiement:read', 'fs_paiement:write'])]
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
