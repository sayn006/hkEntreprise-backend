<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ResponsableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [new GetCollection(), new Get(), new Post(), new Patch(), new Delete()],
    normalizationContext: ['groups' => ['responsable:read']],
    denormalizationContext: ['groups' => ['responsable:write']],
)]
#[ORM\Entity(repositoryClass: ResponsableRepository::class)]
class Responsable
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     #[Groups(['responsable:read'])]
     private ?int $id = null;

     #[ORM\Column(length: 255)]
     #[Groups(['responsable:read', 'responsable:write'])]
     private ?string $nom = null;

     #[ORM\Column(length: 255)]
     #[Groups(['responsable:read', 'responsable:write'])]
     private ?string $prenom = null;

     #[ORM\Column(length: 255)]
     #[Groups(['responsable:read', 'responsable:write'])]
     private ?string $email = null;

     #[ORM\Column(length: 20, nullable: true)]
     #[Groups(['responsable:read', 'responsable:write'])]
     private ?string $telephone = null;

     #[ORM\ManyToMany(targetEntity: Chantier::class)]
     #[Groups(['responsable:read', 'responsable:write'])]
     private Collection $chantiers;

     public function __construct()
     {
          $this->chantiers = new ArrayCollection();
     }

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getNom(): ?string
     {
          return $this->nom;
     }

     public function setNom(string $nom): static
     {
          $this->nom = $nom;
          return $this;
     }

     public function getPrenom(): ?string
     {
          return $this->prenom;
     }

     public function setPrenom(string $prenom): static
     {
          $this->prenom = $prenom;
          return $this;
     }

     public function getEmail(): ?string
     {
          return $this->email;
     }

     public function setEmail(string $email): static
     {
          $this->email = $email;
          return $this;
     }

     public function getTelephone(): ?string
     {
          return $this->telephone;
     }

     public function setTelephone(?string $telephone): static
     {
          $this->telephone = $telephone;
          return $this;
     }

     /**
      * @return Collection<int, Chantier>
      */
     public function getChantiers(): Collection
     {
          return $this->chantiers;
     }

     public function addChantier(Chantier $chantier): static
     {
          if (!$this->chantiers->contains($chantier)) {
               $this->chantiers->add($chantier);
               $chantier->addResponsable($this);
          }

          return $this;
     }

     public function removeChantier(Chantier $chantier): static
     {
          if ($this->chantiers->removeElement($chantier)) {
               $chantier->removeResponsable($this);
          }

          return $this;
     }
}
