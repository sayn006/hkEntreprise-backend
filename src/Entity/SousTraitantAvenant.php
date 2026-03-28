<?php

namespace App\Entity;

use App\Repository\SousTraitantAvenantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SousTraitantAvenantRepository::class)]
#[ORM\Table(name: 'sous_traitant_avenant')]
class SousTraitantAvenant
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\Column(type: Types::DATE_MUTABLE)]
     private ?\DateTimeInterface $date = null;

     #[ORM\Column]
     private ?\DateTimeImmutable $createdAt = null;

     #[ORM\Column(nullable: true)]
     private ?\DateTimeImmutable $updatedAt = null;

     #[ORM\ManyToOne(fetch: 'EAGER')]
     private ?User $createdUser = null;

     #[ORM\ManyToOne(inversedBy: 'avenants', fetch: 'EAGER')]
     #[ORM\JoinColumn(nullable: false)]
     private ?SousTraitantContrat $contrat = null;

     #[ORM\ManyToOne(fetch: 'EAGER')]
     #[ORM\JoinColumn(name: 'fichier_id', referencedColumnName: 'id', nullable: true)]
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
