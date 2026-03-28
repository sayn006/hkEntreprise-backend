<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\DevisAvancementRepository;
use App\State\AvancementCreationProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(processor: AvancementCreationProcessor::class),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['devis_avancement:read']],
    denormalizationContext: ['groups' => ['devis_avancement:write']],
    paginationEnabled: false,
)]
#[ApiFilter(SearchFilter::class, properties: ['devis' => 'exact', 'etat' => 'exact'])]
#[ApiFilter(BooleanFilter::class, properties: ['isDeleted'])]
#[ApiFilter(OrderFilter::class, properties: ['numeroOrdre'])]
#[ORM\Entity(repositoryClass: DevisAvancementRepository::class)]
class DevisAvancement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['devis_avancement:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Devis::class, inversedBy: 'devisAvancements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private ?Devis $devis = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private string $numero;

    #[ORM\Column(type: 'integer')]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private int $numeroOrdre;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private \DateTimeInterface $moisReference;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['devis_avancement:read'])]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private string $etat = 'EtatAvancementEnCours';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private string $totalHT = '0';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private string $totalCumule = '0';

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private string $pourcentageGlobal = '0';

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_avancement:read'])]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['devis_avancement:read'])]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['devis_avancement:read'])]
    private ?User $createdBy = null;

    #[ORM\OneToOne(targetEntity: FactureSituation::class, inversedBy: 'devisAvancement')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['devis_avancement:read', 'devis_avancement:write'])]
    private ?FactureSituation $factureSituation = null;

    /**
     * @var Collection<int, DevisAvancementDetail>
     */
    #[ORM\OneToMany(targetEntity: DevisAvancementDetail::class, mappedBy: 'devisAvancement', cascade: ['persist', 'remove'])]
    #[Groups(['devis_avancement:read'])]
    private Collection $devisAvancementDetails;

    public function __construct()
    {
        $this->devisAvancementDetails = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->moisReference = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevis(): ?Devis
    {
        return $this->devis;
    }

    public function setDevis(?Devis $devis): static
    {
        $this->devis = $devis;
        return $this;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getNumeroOrdre(): int
    {
        return $this->numeroOrdre;
    }

    public function setNumeroOrdre(int $numeroOrdre): static
    {
        $this->numeroOrdre = $numeroOrdre;
        return $this;
    }

    public function getMoisReference(): \DateTimeInterface
    {
        return $this->moisReference;
    }

    public function setMoisReference(\DateTimeInterface $moisReference): static
    {
        $this->moisReference = $moisReference;
        return $this;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getEtat(): string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getTotalHT(): string
    {
        return $this->totalHT;
    }

    public function setTotalHT(float $totalHT): static
    {
        $this->totalHT = $totalHT;
        return $this;
    }

    public function getTotalCumule(): string
    {
        return $this->totalCumule;
    }

    public function setTotalCumule(float $totalCumule): static
    {
        $this->totalCumule = $totalCumule;
        return $this;
    }

    public function getPourcentageGlobal(): string
    {
        return $this->pourcentageGlobal;
    }

    public function setPourcentageGlobal(float $pourcentageGlobal): static
    {
        $this->pourcentageGlobal = $pourcentageGlobal;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function softDelete(): static
    {
        $this->isDeleted = true;
        $this->deletedAt = new \DateTime();
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

    public function getFactureSituation(): ?FactureSituation
    {
        return $this->factureSituation;
    }

    public function setFactureSituation(?FactureSituation $factureSituation): static
    {
        $this->factureSituation = $factureSituation;
        return $this;
    }

    public function hasFactureSituation(): bool
    {
        return $this->factureSituation !== null;
    }

    /**
     * @return Collection<int, DevisAvancementDetail>
     */
    public function getDevisAvancementDetails(): Collection
    {
        return $this->devisAvancementDetails;
    }

    public function addDevisAvancementDetail(DevisAvancementDetail $detail): static
    {
        if (!$this->devisAvancementDetails->contains($detail)) {
            $this->devisAvancementDetails->add($detail);
            $detail->setDevisAvancement($this);
        }
        return $this;
    }

    public function removeDevisAvancementDetail(DevisAvancementDetail $detail): static
    {
        if ($this->devisAvancementDetails->removeElement($detail)) {
            if ($detail->getDevisAvancement() === $this) {
                $detail->setDevisAvancement(null);
            }
        }
        return $this;
    }

    /**
     * Retourne le label d'état lisible
     */
    public function getEtatLabel(): string
    {
        return match ($this->etat) {
            'EtatAvancementEnCours' => 'En cours',
            'EtatAvancementValide' => 'Validé',
            'EtatAvancementAnnule' => 'Annulé',
            default => $this->etat,
        };
    }

    /**
     * Retourne la classe CSS pour le badge d'état
     */
    public function getEtatBadgeClass(): string
    {
        return match ($this->etat) {
            'EtatAvancementEnCours' => 'bg-warning',
            'EtatAvancementValide' => 'bg-success',
            'EtatAvancementAnnule' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
