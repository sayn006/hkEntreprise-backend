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
use App\Repository\FactureSituationTropPercuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['fs_trop_percu:read']],
    denormalizationContext: ['groups' => ['fs_trop_percu:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['factureSituation' => 'exact', 'status' => 'exact'])]
#[ORM\Entity(repositoryClass: FactureSituationTropPercuRepository::class)]
#[ORM\Table(name: 'facture_situation_trop_percu')]
class FactureSituationTropPercu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fs_trop_percu:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FactureSituation::class, inversedBy: 'tropPercus')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['fs_trop_percu:read', 'fs_trop_percu:write'])]
    private ?FactureSituation $factureSituation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant est obligatoire')]
    #[Assert\Positive(message: 'Le montant doit être positif')]
    #[Groups(['fs_trop_percu:read', 'fs_trop_percu:write'])]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    #[Groups(['fs_trop_percu:read'])]
    private ?string $montantUtilise = '0.00';

    #[ORM\Column(length: 50, options: ['default' => 'disponible'])]
    #[Groups(['fs_trop_percu:read', 'fs_trop_percu:write'])]
    private ?string $status = 'disponible';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['fs_trop_percu:read'])]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['fs_trop_percu:read', 'fs_trop_percu:write'])]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['fs_trop_percu:read', 'fs_trop_percu:write'])]
    private ?string $commentaire = null;

    /**
     * @var Collection<int, FactureSituationTropPercuUtilisation>
     */
    #[ORM\OneToMany(targetEntity: FactureSituationTropPercuUtilisation::class, mappedBy: 'tropPercu', orphanRemoval: true)]
    #[ORM\OrderBy(['dateUtilisation' => 'DESC'])]
    #[Groups(['fs_trop_percu:read'])]
    private Collection $utilisations;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->montantUtilise = '0.00';
        $this->status = 'disponible';
        $this->utilisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFactureSituation(): ?FactureSituation
    {
        return $this->factureSituation;
    }

    public function setFactureSituation(?FactureSituation $factureSituation): static
    {
        $this->factureSituation = $factureSituation;
        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getMontantUtilise(): ?string
    {
        return $this->montantUtilise;
    }

    public function setMontantUtilise(string $montantUtilise): static
    {
        $this->montantUtilise = $montantUtilise;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    /**
     * @return Collection<int, FactureSituationTropPercuUtilisation>
     */
    public function getUtilisations(): Collection
    {
        return $this->utilisations;
    }

    public function addUtilisation(FactureSituationTropPercuUtilisation $utilisation): static
    {
        if (!$this->utilisations->contains($utilisation)) {
            $this->utilisations->add($utilisation);
            $utilisation->setTropPercu($this);
        }

        return $this;
    }

    public function removeUtilisation(FactureSituationTropPercuUtilisation $utilisation): static
    {
        if ($this->utilisations->removeElement($utilisation)) {
            if ($utilisation->getTropPercu() === $this) {
                $utilisation->setTropPercu(null);
            }
        }

        return $this;
    }

    /**
     * Calcule le montant encore disponible
     */
    public function getMontantDisponible(): float
    {
        return (float) $this->montant - (float) $this->montantUtilise;
    }

    /**
     * Vérifie si le trop-perçu est complètement utilisé
     */
    public function isFullyUtilized(): bool
    {
        return $this->getMontantDisponible() <= 0;
    }

    /**
     * Vérifie si le trop-perçu est partiellement utilisé
     */
    public function isPartiallyUtilized(): bool
    {
        $montantUtilise = (float) $this->montantUtilise;
        return $montantUtilise > 0 && $montantUtilise < (float) $this->montant;
    }
}
