<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\DevisAvancementDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiFilter(SearchFilter::class, properties: ['devisAvancement' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['devis_avancement_detail:read']],
    denormalizationContext: ['groups' => ['devis_avancement_detail:write']],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: DevisAvancementDetailRepository::class)]
class DevisAvancementDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: DevisAvancement::class, inversedBy: 'devisAvancementDetails')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write'])]
    private ?DevisAvancement $devisAvancement = null;

    #[ORM\ManyToOne(targetEntity: DevisDetail::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write'])]
    private ?DevisDetail $devisDetail = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $reference = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $designation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?int $quantite = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $unite = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $totalDevis = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement:read'])]
    private string $pourcentageMoins1 = '0';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement:read'])]
    private string $totalHTMoins1 = '0';

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private string $pourcentage = '0';

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement:read'])]
    private string $totalHT = '0';

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $blockNumber = null;

    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'RL'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private string $lineType = 'RL';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private ?string $lineSubType = null;

    #[ORM\Column(type: 'integer', options: ['default' => '0'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private int $displayOrder = 0;

    #[ORM\Column(type: 'integer', options: ['default' => '0'])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private int $orderInBlock = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private bool $isBlockHeader = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private bool $isBlockTotal = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private bool $isBlockFooter = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_avancement_detail:read', 'devis_avancement_detail:write', 'devis_avancement:read'])]
    private bool $isTravauxSupplementaires = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_avancement_detail:read'])]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['devis_avancement_detail:read'])]
    private ?\DateTimeInterface $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevisAvancement(): ?DevisAvancement
    {
        return $this->devisAvancement;
    }

    public function setDevisAvancement(?DevisAvancement $devisAvancement): static
    {
        $this->devisAvancement = $devisAvancement;
        return $this;
    }

    public function getDevisDetail(): ?DevisDetail
    {
        return $this->devisDetail;
    }

    public function setDevisDetail(?DevisDetail $devisDetail): static
    {
        $this->devisDetail = $devisDetail;
        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): static
    {
        $this->unite = $unite;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getTotalDevis(): ?float
    {
        return $this->totalDevis;
    }

    public function setTotalDevis(?float $totalDevis): static
    {
        $this->totalDevis = $totalDevis;
        return $this;
    }

    public function getPourcentageMoins1(): string
    {
        return $this->pourcentageMoins1;
    }

    public function setPourcentageMoins1(float $pourcentageMoins1): static
    {
        $this->pourcentageMoins1 = $pourcentageMoins1;
        return $this;
    }

    public function getTotalHTMoins1(): string
    {
        return $this->totalHTMoins1;
    }

    public function setTotalHTMoins1(float $totalHTMoins1): static
    {
        $this->totalHTMoins1 = $totalHTMoins1;
        return $this;
    }

    public function getPourcentage(): string
    {
        return $this->pourcentage;
    }

    public function setPourcentage(float $pourcentage): static
    {
        $this->pourcentage = $pourcentage;
        // Calcul automatique du Total HT
        if ($this->totalDevis !== null) {
            $this->totalHT = ($pourcentage / 100) * $this->totalDevis;
        }
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

    public function getBlockNumber(): ?string
    {
        return $this->blockNumber;
    }

    public function setBlockNumber(?string $blockNumber): static
    {
        $this->blockNumber = $blockNumber;
        return $this;
    }

    /**
     * Retourne un identifiant CSS-safe pour le bloc
     */
    public function getBlockId(): ?string
    {
        return $this->blockNumber ? str_replace('.', '-', $this->blockNumber) : null;
    }

    public function getLineType(): string
    {
        return $this->lineType;
    }

    public function setLineType(string $lineType): static
    {
        $this->lineType = $lineType;
        return $this;
    }

    public function getLineSubType(): ?string
    {
        return $this->lineSubType;
    }

    public function setLineSubType(?string $lineSubType): static
    {
        $this->lineSubType = $lineSubType;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): static
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function getOrderInBlock(): int
    {
        return $this->orderInBlock;
    }

    public function setOrderInBlock(int $orderInBlock): static
    {
        $this->orderInBlock = $orderInBlock;
        return $this;
    }

    public function isBlockHeader(): bool
    {
        return $this->isBlockHeader;
    }

    public function setIsBlockHeader(bool $isBlockHeader): static
    {
        $this->isBlockHeader = $isBlockHeader;
        return $this;
    }

    public function isBlockTotal(): bool
    {
        return $this->isBlockTotal;
    }

    public function setIsBlockTotal(bool $isBlockTotal): static
    {
        $this->isBlockTotal = $isBlockTotal;
        return $this;
    }

    public function isBlockFooter(): bool
    {
        return $this->isBlockFooter;
    }

    public function setIsBlockFooter(bool $isBlockFooter): static
    {
        $this->isBlockFooter = $isBlockFooter;
        return $this;
    }

    /**
     * Copie les données depuis une ligne de devis
     */
    public function copyFromDevisDetail(DevisDetail $detail): static
    {
        $this->devisDetail = $detail;
        $this->reference = $detail->getReference();
        $this->designation = $detail->getDesignation();
        $this->quantite = $detail->getQuantite();
        $this->unite = $detail->getUnite();
        $this->prixUnitaire = $detail->getPrixUnitaire();
        $this->totalDevis = $detail->getTotal();
        $this->blockNumber = $detail->getBlockNumber();
        $this->lineType = $detail->getLineType();
        $this->lineSubType = $detail->getLineSubType();
        $this->displayOrder = $detail->getDisplayOrder();
        $this->orderInBlock = $detail->getOrderInBlock();
        $this->isBlockHeader = $detail->isBlockHeader();
        $this->isBlockTotal = $detail->isBlockTotal();
        $this->isBlockFooter = $detail->isBlockFooter();

        return $this;
    }

    /**
     * Copie les données du mois précédent
     */
    public function copyFromPreviousMonth(DevisAvancementDetail $previous): static
    {
        $this->pourcentageMoins1 = $previous->getPourcentage();
        $this->totalHTMoins1 = $previous->getTotalHT();

        return $this;
    }

    public function isTravauxSupplementaires(): bool
    {
        return $this->isTravauxSupplementaires;
    }

    public function setIsTravauxSupplementaires(bool $isTravauxSupplementaires): static
    {
        $this->isTravauxSupplementaires = $isTravauxSupplementaires;
        return $this;
    }

    /**
     * Alias exposé à l'API (Symfony serializer ne détecte pas toujours les getters is*()
     * sur une propriété nommée "isFoo"). Retourne le même flag.
     */
    #[Groups(['devis_avancement_detail:read', 'devis_avancement:read'])]
    public function getTravauxSup(): bool
    {
        return $this->isTravauxSupplementaires;
    }

    /**
     * Copie les données depuis un travail supplémentaire de l'avancement précédent
     */
    public function copyFromTravauxSupplementaires(DevisAvancementDetail $source): static
    {
        $this->isTravauxSupplementaires = true;
        $this->reference = $source->getReference();
        $this->designation = $source->getDesignation();
        $this->quantite = $source->getQuantite();
        $this->unite = $source->getUnite();
        $this->prixUnitaire = $source->getPrixUnitaire();
        $this->totalDevis = $source->getTotalDevis();
        $this->blockNumber = $source->getBlockNumber();
        $this->lineType = $source->getLineType();
        $this->lineSubType = $source->getLineSubType();
        $this->displayOrder = $source->getDisplayOrder();
        $this->orderInBlock = $source->getOrderInBlock();
        $this->isBlockHeader = $source->isBlockHeader();
        $this->isBlockTotal = $source->isBlockTotal();
        $this->isBlockFooter = $source->isBlockFooter();

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

    /**
     * Soft delete - marque comme supprimé sans supprimer physiquement
     */
    public function softDelete(): static
    {
        $this->isDeleted = true;
        $this->deletedAt = new \DateTime();
        return $this;
    }
}
