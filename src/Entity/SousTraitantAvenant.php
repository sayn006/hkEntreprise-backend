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
use App\Repository\SousTraitantAvenantRepository;
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
    normalizationContext: ['groups' => ['sous_traitant_avenant:read', 'sous_traitant:read']],
    denormalizationContext: ['groups' => ['sous_traitant_avenant:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['contrat' => 'exact'])]
#[ORM\Entity(repositoryClass: SousTraitantAvenantRepository::class)]
#[ORM\Table(name: 'sous_traitant_avenant')]
class SousTraitantAvenant
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     #[Groups(['sous_traitant_avenant:read', 'sous_traitant_contrat:read', 'sous_traitant:read'])]
     private ?int $id = null;

     #[ORM\Column(type: Types::DATE_MUTABLE)]
     #[Groups(['sous_traitant_avenant:read', 'sous_traitant_avenant:write', 'sous_traitant_contrat:read', 'sous_traitant:read'])]
     private ?\DateTimeInterface $date = null;

     #[ORM\Column]
     #[Groups(['sous_traitant_avenant:read', 'sous_traitant_contrat:read'])]
     private ?\DateTimeImmutable $createdAt = null;

     #[ORM\Column(nullable: true)]
     #[Groups(['sous_traitant_avenant:read'])]
     private ?\DateTimeImmutable $updatedAt = null;

     #[ORM\ManyToOne(fetch: 'EAGER')]
     #[Groups(['sous_traitant_avenant:read'])]
     private ?User $createdUser = null;

     #[ORM\ManyToOne(inversedBy: 'avenants', fetch: 'EAGER')]
     #[ORM\JoinColumn(nullable: false)]
     #[Groups(['sous_traitant_avenant:read', 'sous_traitant_avenant:write'])]
     private ?SousTraitantContrat $contrat = null;

     #[ORM\ManyToOne(fetch: 'EAGER')]
     #[ORM\JoinColumn(name: 'fichier_id', referencedColumnName: 'id', nullable: true)]
     #[Groups(['sous_traitant_avenant:read', 'sous_traitant_avenant:write'])]
     private ?Uploads $fichier = null;

     public function __construct()
     {
          $this->createdAt = new \DateTimeImmutable();
     }

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getDate(): ?\DateTimeInterface
     {
          return $this->date;
     }

     public function setDate(\DateTimeInterface $date): static
     {
          $this->date = $date;

          return $this;
     }

     public function getCreatedAt(): ?\DateTimeImmutable
     {
          return $this->createdAt;
     }

     public function setCreatedAt(\DateTimeImmutable $createdAt): static
     {
          $this->createdAt = $createdAt;

          return $this;
     }

     public function getUpdatedAt(): ?\DateTimeImmutable
     {
          return $this->updatedAt;
     }

     public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
     {
          $this->updatedAt = $updatedAt;

          return $this;
     }

     public function getCreatedUser(): ?User
     {
          return $this->createdUser;
     }

     public function setCreatedUser(?User $createdUser): static
     {
          $this->createdUser = $createdUser;

          return $this;
     }

     public function getContrat(): ?SousTraitantContrat
     {
          return $this->contrat;
     }

     public function setContrat(?SousTraitantContrat $contrat): static
     {
          $this->contrat = $contrat;

          return $this;
     }

     public function getFichier(): ?Uploads
     {
          return $this->fichier;
     }

     public function setFichier(?Uploads $fichier): static
     {
          $this->fichier = $fichier;

          return $this;
     }

     public function __toString(): string
     {
          return 'Avenant ' . $this->contrat?->getSousTraitant()?->getRaisonSocial() . ' - ' . $this->date?->format('d/m/Y');
     }
}
