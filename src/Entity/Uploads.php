<?php

namespace App\Entity;

use App\Repository\UploadsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UploadsRepository::class)]
class Uploads
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uniqueFileName = null;

    #[ORM\ManyToOne]
    private ?DocumentType $documentType = null;

    #[ORM\ManyToOne]
    private ?User $createdUser = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entity_type = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SuiviFacture", inversedBy="Uploads")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
     */
    private $suiviFacture;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $upload_option = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $softDelete = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getSuiviFacture(): ?SuiviFacture
    {
        return $this->suiviFacture;
    }

    public function setSuiviFacture(?SuiviFacture $suiviFacture): self
    {
        $this->suiviFacture = $suiviFacture;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getUniqueFileName(): ?string
    {
        return $this->uniqueFileName;
    }

    public function setUniqueFileName(?string $uniqueFileName): static
    {
        $this->uniqueFileName = $uniqueFileName;

        return $this;
    }


    public function getDocumentType(): ?DocumentType
    {
        return $this->documentType;
    }

    public function setDocumentType(?DocumentType $documentType): static
    {
        $this->documentType = $documentType;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entity_type;
    }

    public function setEntityType(?string $entity_type): static
    {
        $this->entity_type = $entity_type;

        return $this;
    }

    public function getUploadOption(): ?string
    {
        return $this->upload_option;
    }

    public function setUploadOption(?string $upload_option): static
    {
        $this->upload_option = $upload_option;

        return $this;
    }

    public function isSoftDelete(): ?bool
    {
        return $this->softDelete;
    }

    public function setSoftDelete(bool $softDelete): static
    {
        $this->softDelete = $softDelete;

        return $this;
    }
}
