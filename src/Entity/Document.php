<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\Column(length: 255)]
     private ?string $nom = null;

     #[ORM\Column]
     private ?\DateTimeImmutable $createdAt = null;

     public function __construct()
     {
          $this->createdAt = new \DateTimeImmutable();
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

     public function getCreatedAt(): ?\DateTimeImmutable
     {
          return $this->createdAt;
     }

     public function setCreatedAt(\DateTimeImmutable $createdAt): static
     {
          $this->createdAt = $createdAt;
          return $this;
     }
}
