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
use App\Repository\SousTraitantContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    normalizationContext: ['groups' => ['sous_traitant_contrat:read', 'sous_traitant:read']],
    denormalizationContext: ['groups' => ['sous_traitant_contrat:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['sousTraitant' => 'exact'])]
#[ORM\Entity(repositoryClass: SousTraitantContratRepository::class)]
#[ORM\Table(name: 'sous_traitant_contrat')]
class SousTraitantContrat
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     #[Groups(['sous_traitant_contrat:read', 'sous_traitant:read'])]
     private ?int $id = null;

     #[ORM\Column(type: Types::DATE_MUTABLE)]
     #[Groups(['sous_traitant_contrat:read', 'sous_traitant_contrat:write', 'sous_traitant:read'])]
     private ?\DateTimeInterface $date = null;

     #[ORM\Column]
     #[Groups(['sous_traitant_contrat:read', 'sous_traitant:read'])]
     private ?\DateTimeImmutable $createdAt = null;

     #[ORM\Column(nullable: true)]
     #[Groups(['sous_traitant_contrat:read'])]
     private ?\DateTimeImmutable $updatedAt = null;

     #[ORM\ManyToOne(fetch: 'EAGER')]
     #[Groups(['sous_traitant_contrat:read'])]
     private ?User $createdUser = null;

     #[ORM\ManyToOne(inversedBy: 'contrats', fetch: 'EAGER')]
     #[ORM\JoinColumn(nullable: false)]
     #[Groups(['sous_traitant_contrat:read', 'sous_traitant_contrat:write'])]
     private ?SousTraitent $sousTraitant = null;

     #[ORM\ManyToOne(fetch: 'EAGER')]
     #[ORM\JoinColumn(name: 'fichier_id', referencedColumnName: 'id', nullable: true)]
     #[Groups(['sous_traitant_contrat:read', 'sous_traitant_contrat:write'])]
     private ?Uploads $fichier = null;

     #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: SousTraitantAvenant::class, cascade: ['persist', 'remove'], fetch: 'LAZY')]
     #[Groups(['sous_traitant_contrat:read', 'sous_traitant:read'])]
     private Collection $avenants;


     public function __construct()
     {
          $this->createdAt = new \DateTimeImmutable();
          $this->avenants = new ArrayCollection();
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

     public function getSousTraitant(): ?SousTraitent
     {
          return $this->sousTraitant;
     }

     public function setSousTraitant(?SousTraitent $sousTraitant): static
     {
          $this->sousTraitant = $sousTraitant;

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


     /**
      * @return Collection<int, SousTraitantAvenant>
      */
     public function getAvenants(): Collection
     {
          return $this->avenants;
     }

     public function addAvenant(SousTraitantAvenant $avenant): static
     {
          if (!$this->avenants->contains($avenant)) {
               $this->avenants->add($avenant);
               $avenant->setContrat($this);
          }

          return $this;
     }

     public function removeAvenant(SousTraitantAvenant $avenant): static
     {
          if ($this->avenants->removeElement($avenant)) {
               // set the owning side to null (unless already changed)
               if ($avenant->getContrat() === $this) {
                    $avenant->setContrat(null);
               }
          }

          return $this;
     }

     public function __toString(): string
     {
          return 'Contrat ' . $this->sousTraitant?->getRaisonSocial() . ' - ' . $this->date?->format('d/m/Y');
     }
}
