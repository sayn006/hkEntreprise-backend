<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\DevisDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [new GetCollection(), new Get(), new Post(), new Patch(), new Delete()],
    normalizationContext: ['groups' => ['devis_detail:read']],
    denormalizationContext: ['groups' => ['devis_detail:write']],
    paginationEnabled: false,
)]
#[ApiFilter(SearchFilter::class, properties: ['devis' => 'exact', 'type' => 'exact'])]
#[ApiFilter(BooleanFilter::class, properties: ['isDeleted'])]
#[ApiFilter(OrderFilter::class, properties: ['displayOrder'])]
#[ORM\Entity(repositoryClass: DevisDetailRepository::class)]
class DevisDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['devis_detail:read', 'devis:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $reference = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $designation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?int $quantite = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?float $prixUnitaire = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $coefficientMateriel = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $totalMateriel = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $moUnit = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $coefficientMainOeuvre = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $tauxMainOeuvre = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $moTotal = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $total = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $pvUnit = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $tva = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $groupId = null;

    #[ORM\ManyToOne(inversedBy: 'devisDetails')]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?Devis $devis = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $sousType = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $bloc = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $numeroEnsemble = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?float $tempsDePose = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?float $ecoTaxe = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?float $remise = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $unite = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $zone = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $codeVentilation1 = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $codeVentilation2 = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $blockNumber = null;

    #[ORM\Column(type: 'string', length: 10, options: ['default' => 'RL'])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private string $lineType = 'RL';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private ?string $lineSubType = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private bool $isBlockHeader = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private bool $isBlockTotal = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private bool $isBlockFooter = false;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private int $displayOrder = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private int $orderInBlock = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis_detail:read', 'devis_detail:write'])]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['devis_detail:read'])]
    private ?\DateTimeInterface $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(?float $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getCoefficientMateriel(): ?float
    {
        return $this->coefficientMateriel;
    }

    public function setCoefficientMateriel(?float $coefficientMateriel): static
    {
        $this->coefficientMateriel = $coefficientMateriel;
        return $this;
    }

    public function getTotalMateriel(): ?float
    {
        return $this->totalMateriel;
    }

    public function setTotalMateriel(?float $totalMateriel): static
    {
        $this->totalMateriel = $totalMateriel;
        return $this;
    }

    public function getMoUnit(): ?float
    {
        return $this->moUnit;
    }

    public function setMoUnit(?float $moUnit): static
    {
        $this->moUnit = $moUnit;
        return $this;
    }

    public function getCoefficientMainOeuvre(): ?float
    {
        return $this->coefficientMainOeuvre;
    }

    public function setCoefficientMainOeuvre(?float $coefficientMainOeuvre): static
    {
        $this->coefficientMainOeuvre = $coefficientMainOeuvre;
        return $this;
    }

    public function getTauxMainOeuvre(): ?float
    {
        return $this->tauxMainOeuvre;
    }

    public function setTauxMainOeuvre(?float $tauxMainOeuvre): static
    {
        $this->tauxMainOeuvre = $tauxMainOeuvre;
        return $this;
    }

    public function getMoTotal(): ?float
    {
        return $this->moTotal;
    }

    public function setMoTotal(?float $moTotal): static
    {
        $this->moTotal = $moTotal;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getPvUnit(): ?float
    {
        return $this->pvUnit;
    }

    public function setPvUnit(?float $pvUnit): static
    {
        $this->pvUnit = $pvUnit;
        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): static
    {
        $this->tva = $tva;
        return $this;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): static
    {
        $this->groupId = $groupId;
        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSousType(): ?string
    {
        return $this->sousType;
    }

    public function setSousType(?string $sousType): static
    {
        $this->sousType = $sousType;
        return $this;
    }

    public function getBloc(): ?string
    {
        return $this->bloc;
    }

    public function setBloc(?string $bloc): static
    {
        $this->bloc = $bloc;
        return $this;
    }

    public function getNumeroEnsemble(): ?string
    {
        return $this->numeroEnsemble;
    }

    public function setNumeroEnsemble(?string $numeroEnsemble): static
    {
        $this->numeroEnsemble = $numeroEnsemble;
        return $this;
    }

    public function getTempsDePose(): ?float
    {
        return $this->tempsDePose;
    }

    public function setTempsDePose(?float $tempsDePose): static
    {
        $this->tempsDePose = $tempsDePose;
        return $this;
    }

    public function getEcoTaxe(): ?float
    {
        return $this->ecoTaxe;
    }

    public function setEcoTaxe(?float $ecoTaxe): static
    {
        $this->ecoTaxe = $ecoTaxe;
        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(?float $remise): static
    {
        $this->remise = $remise;
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

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        $this->zone = $zone;
        return $this;
    }

    public function getCodeVentilation1(): ?string
    {
        return $this->codeVentilation1;
    }

    public function setCodeVentilation1(?string $codeVentilation1): static
    {
        $this->codeVentilation1 = $codeVentilation1;
        return $this;
    }

    public function getCodeVentilation2(): ?string
    {
        return $this->codeVentilation2;
    }

    public function setCodeVentilation2(?string $codeVentilation2): static
    {
        $this->codeVentilation2 = $codeVentilation2;
        return $this;
    }

    // Getters et Setters pour les nouveaux champs de blocs

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
     * Retourne un identifiant CSS-safe pour le bloc (remplace les points par des tirets)
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

    public function getIsBlockHeader(): bool
    {
        return $this->isBlockHeader;
    }

    public function setIsBlockHeader(bool $isBlockHeader): static
    {
        $this->isBlockHeader = $isBlockHeader;
        return $this;
    }

    public function getIsBlockTotal(): bool
    {
        return $this->isBlockTotal;
    }

    public function setIsBlockTotal(bool $isBlockTotal): static
    {
        $this->isBlockTotal = $isBlockTotal;
        return $this;
    }

    public function getIsBlockFooter(): bool
    {
        return $this->isBlockFooter;
    }

    public function setIsBlockFooter(bool $isBlockFooter): static
    {
        $this->isBlockFooter = $isBlockFooter;
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

    public function getIsDeleted(): bool
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
     * Soft delete ce détail
     */
    public function softDelete(): static
    {
        $this->isDeleted = true;
        $this->deletedAt = new \DateTime();
        return $this;
    }
}
