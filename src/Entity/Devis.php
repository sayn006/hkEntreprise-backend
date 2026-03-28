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
use App\Repository\DevisRepository;
use App\State\DevisAcceptationProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(processor: DevisAcceptationProcessor::class),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['devis:read']],
    denormalizationContext: ['groups' => ['devis:write']],
    paginationEnabled: false,
)]
#[ApiFilter(BooleanFilter::class, properties: ['isDeleted'])]
#[ApiFilter(SearchFilter::class, properties: ['etat' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['dateCreation', 'numero'])]
#[ORM\Entity(repositoryClass: DevisRepository::class)]
class Devis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['devis:read', 'devis_avancement:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['devis:read', 'devis:write', 'devis_avancement:read'])]
    private string $titre;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Groups(['devis:read', 'devis:write', 'devis_avancement:read'])]
    private string $numero;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['devis:read', 'devis:write'])]
    private ?string $nomInterlocuteur = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['devis:read', 'devis:write'])]
    private ?string $prenomInterlocuteur = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['devis:read', 'devis:write'])]
    private \DateTimeInterface $dateCreation;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tva1 = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tva2 = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tva3 = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $tva4 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $repartition1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $repartition2 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $repartition3 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tri1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tri2 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tri3 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ventilation1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ventilation2 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateRemise = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['devis:read', 'devis:write'])]
    private ?string $type = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $subvention = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseChantierTitre = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseChantierAdresse1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseChantierAdresse2 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseChantierAdresse3 = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $adresseChantierCodePostal = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseChantierVille = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseChantierPays = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['devis:read', 'devis:write'])]
    private string $coefficientMateriel;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['devis:read', 'devis:write'])]
    private string $coefficientMainOeuvre;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['devis:read', 'devis:write'])]
    private string $tauxMainOeuvre;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $modePose = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $prixConseilles = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseExpeditionTitre = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseExpeditionAdresse1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseExpeditionAdresse2 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseExpeditionAdresse3 = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $adresseExpeditionCodePostal = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseExpeditionVille = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseExpeditionPays = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['devis:read', 'devis:write', 'devis_avancement:read'])]
    private string $etat;

    /**
     * @var Collection<int, DevisDetail>
     */
    #[ORM\OneToMany(targetEntity: DevisDetail::class, mappedBy: 'devis')]
    private Collection $devisDetails;

    #[ORM\ManyToOne(targetEntity: Chantier::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['devis:read', 'devis:write'])]
    private ?Chantier $chantier = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $codeClient = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['devis:read', 'devis:write'])]
    private bool $isDeleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    /**
     * @var Collection<int, DevisAvancement>
     */
    #[ORM\OneToMany(targetEntity: DevisAvancement::class, mappedBy: 'devis')]
    private Collection $devisAvancements;

    public function __construct()
    {
        $this->devisDetails = new ArrayCollection();
        $this->devisAvancements = new ArrayCollection();
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): void
    {
        $this->numero = $numero;
    }

    public function getNomInterlocuteur(): ?string
    {
        return $this->nomInterlocuteur;
    }

    public function setNomInterlocuteur(?string $nomInterlocuteur): void
    {
        $this->nomInterlocuteur = $nomInterlocuteur;
    }

    public function getPrenomInterlocuteur(): ?string
    {
        return $this->prenomInterlocuteur;
    }

    public function setPrenomInterlocuteur(?string $prenomInterlocuteur): void
    {
        $this->prenomInterlocuteur = $prenomInterlocuteur;
    }

    public function getDateCreation(): \DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    public function getTva1(): ?float
    {
        return $this->tva1;
    }

    public function setTva1(?float $tva1): void
    {
        $this->tva1 = $tva1;
    }

    public function getTva2(): ?float
    {
        return $this->tva2;
    }

    public function setTva2(?float $tva2): void
    {
        $this->tva2 = $tva2;
    }

    public function getTva3(): ?float
    {
        return $this->tva3;
    }

    public function setTva3(?float $tva3): void
    {
        $this->tva3 = $tva3;
    }

    public function getTva4(): ?float
    {
        return $this->tva4;
    }

    public function setTva4(?float $tva4): void
    {
        $this->tva4 = $tva4;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function getRepartition1(): ?string
    {
        return $this->repartition1;
    }

    public function setRepartition1(?string $repartition1): void
    {
        $this->repartition1 = $repartition1;
    }

    public function getRepartition2(): ?string
    {
        return $this->repartition2;
    }

    public function setRepartition2(?string $repartition2): void
    {
        $this->repartition2 = $repartition2;
    }

    public function getRepartition3(): ?string
    {
        return $this->repartition3;
    }

    public function setRepartition3(?string $repartition3): void
    {
        $this->repartition3 = $repartition3;
    }

    public function getTri1(): ?string
    {
        return $this->tri1;
    }

    public function setTri1(?string $tri1): void
    {
        $this->tri1 = $tri1;
    }

    public function getTri2(): ?string
    {
        return $this->tri2;
    }

    public function setTri2(?string $tri2): void
    {
        $this->tri2 = $tri2;
    }

    public function getTri3(): ?string
    {
        return $this->tri3;
    }

    public function setTri3(?string $tri3): void
    {
        $this->tri3 = $tri3;
    }

    public function getVentilation1(): ?string
    {
        return $this->ventilation1;
    }

    public function setVentilation1(?string $ventilation1): void
    {
        $this->ventilation1 = $ventilation1;
    }

    public function getVentilation2(): ?string
    {
        return $this->ventilation2;
    }

    public function setVentilation2(?string $ventilation2): void
    {
        $this->ventilation2 = $ventilation2;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): void
    {
        $this->reference = $reference;
    }

    public function getDateRemise(): ?\DateTimeInterface
    {
        return $this->dateRemise;
    }

    public function setDateRemise(?\DateTimeInterface $dateRemise): void
    {
        $this->dateRemise = $dateRemise;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getSubvention(): ?float
    {
        return $this->subvention;
    }

    public function setSubvention(?float $subvention): void
    {
        $this->subvention = $subvention;
    }

    public function getAdresseChantierTitre(): ?string
    {
        return $this->adresseChantierTitre;
    }

    public function setAdresseChantierTitre(?string $adresseChantierTitre): void
    {
        $this->adresseChantierTitre = $adresseChantierTitre;
    }

    public function getAdresseChantierAdresse1(): ?string
    {
        return $this->adresseChantierAdresse1;
    }

    public function setAdresseChantierAdresse1(?string $adresseChantierAdresse1): void
    {
        $this->adresseChantierAdresse1 = $adresseChantierAdresse1;
    }

    public function getAdresseChantierAdresse2(): ?string
    {
        return $this->adresseChantierAdresse2;
    }

    public function setAdresseChantierAdresse2(?string $adresseChantierAdresse2): void
    {
        $this->adresseChantierAdresse2 = $adresseChantierAdresse2;
    }

    public function getAdresseChantierAdresse3(): ?string
    {
        return $this->adresseChantierAdresse3;
    }

    public function setAdresseChantierAdresse3(?string $adresseChantierAdresse3): void
    {
        $this->adresseChantierAdresse3 = $adresseChantierAdresse3;
    }

    public function getAdresseChantierCodePostal(): ?string
    {
        return $this->adresseChantierCodePostal;
    }

    public function setAdresseChantierCodePostal(?string $adresseChantierCodePostal): void
    {
        $this->adresseChantierCodePostal = $adresseChantierCodePostal;
    }

    public function getAdresseChantierVille(): ?string
    {
        return $this->adresseChantierVille;
    }

    public function setAdresseChantierVille(?string $adresseChantierVille): void
    {
        $this->adresseChantierVille = $adresseChantierVille;
    }

    public function getAdresseChantierPays(): ?string
    {
        return $this->adresseChantierPays;
    }

    public function setAdresseChantierPays(?string $adresseChantierPays): void
    {
        $this->adresseChantierPays = $adresseChantierPays;
    }

    public function getCoefficientMateriel(): float
    {
        return $this->coefficientMateriel;
    }

    public function setCoefficientMateriel(float $coefficientMateriel): void
    {
        $this->coefficientMateriel = $coefficientMateriel;
    }

    public function getCoefficientMainOeuvre(): float
    {
        return $this->coefficientMainOeuvre;
    }

    public function setCoefficientMainOeuvre(float $coefficientMainOeuvre): void
    {
        $this->coefficientMainOeuvre = $coefficientMainOeuvre;
    }

    public function getTauxMainOeuvre(): float
    {
        return $this->tauxMainOeuvre;
    }

    public function setTauxMainOeuvre(float $tauxMainOeuvre): void
    {
        $this->tauxMainOeuvre = $tauxMainOeuvre;
    }

    public function getModePose(): ?string
    {
        return $this->modePose;
    }

    public function setModePose(?string $modePose): void
    {
        $this->modePose = $modePose;
    }

    public function isPrixConseilles(): ?bool
    {
        return $this->prixConseilles;
    }

    public function setPrixConseilles(?bool $prixConseilles): void
    {
        $this->prixConseilles = $prixConseilles;
    }

    public function getAdresseExpeditionTitre(): ?string
    {
        return $this->adresseExpeditionTitre;
    }

    public function setAdresseExpeditionTitre(?string $adresseExpeditionTitre): void
    {
        $this->adresseExpeditionTitre = $adresseExpeditionTitre;
    }

    public function getAdresseExpeditionAdresse1(): ?string
    {
        return $this->adresseExpeditionAdresse1;
    }

    public function setAdresseExpeditionAdresse1(?string $adresseExpeditionAdresse1): void
    {
        $this->adresseExpeditionAdresse1 = $adresseExpeditionAdresse1;
    }

    public function getAdresseExpeditionAdresse2(): ?string
    {
        return $this->adresseExpeditionAdresse2;
    }

    public function setAdresseExpeditionAdresse2(?string $adresseExpeditionAdresse2): void
    {
        $this->adresseExpeditionAdresse2 = $adresseExpeditionAdresse2;
    }

    public function getAdresseExpeditionAdresse3(): ?string
    {
        return $this->adresseExpeditionAdresse3;
    }

    public function setAdresseExpeditionAdresse3(?string $adresseExpeditionAdresse3): void
    {
        $this->adresseExpeditionAdresse3 = $adresseExpeditionAdresse3;
    }

    public function getAdresseExpeditionCodePostal(): ?string
    {
        return $this->adresseExpeditionCodePostal;
    }

    public function setAdresseExpeditionCodePostal(?string $adresseExpeditionCodePostal): void
    {
        $this->adresseExpeditionCodePostal = $adresseExpeditionCodePostal;
    }

    public function getAdresseExpeditionVille(): ?string
    {
        return $this->adresseExpeditionVille;
    }

    public function setAdresseExpeditionVille(?string $adresseExpeditionVille): void
    {
        $this->adresseExpeditionVille = $adresseExpeditionVille;
    }

    public function getAdresseExpeditionPays(): ?string
    {
        return $this->adresseExpeditionPays;
    }

    public function setAdresseExpeditionPays(?string $adresseExpeditionPays): void
    {
        $this->adresseExpeditionPays = $adresseExpeditionPays;
    }

    public function getEtat(): string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): void
    {
        $this->etat = $etat;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Collection<int, DevisDetail>
     */
    public function getDevisDetails(): Collection
    {
        return $this->devisDetails;
    }

    public function addDevisDetail(DevisDetail $devisDetail): static
    {
        if (!$this->devisDetails->contains($devisDetail)) {
            $this->devisDetails->add($devisDetail);
            $devisDetail->setDevis($this);
        }

        return $this;
    }

    public function removeDevisDetail(DevisDetail $devisDetail): static
    {
        if ($this->devisDetails->removeElement($devisDetail)) {
            // set the owning side to null (unless already changed)
            if ($devisDetail->getDevis() === $this) {
                $devisDetail->setDevis(null);
            }
        }

        return $this;
    }

    public function getChantier(): ?Chantier
    {
        return $this->chantier;
    }

    public function setChantier(?Chantier $chantier): void
    {
        $this->chantier = $chantier;
    }

    public function getCodeClient(): ?string
    {
        return $this->codeClient;
    }

    public function setCodeClient(?string $codeClient): void
    {
        $this->codeClient = $codeClient;
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
     * Soft delete le devis et tous ses détails
     */
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

    /**
     * @return Collection<int, DevisAvancement>
     */
    public function getDevisAvancements(): Collection
    {
        return $this->devisAvancements;
    }

    public function addDevisAvancement(DevisAvancement $devisAvancement): static
    {
        if (!$this->devisAvancements->contains($devisAvancement)) {
            $this->devisAvancements->add($devisAvancement);
            $devisAvancement->setDevis($this);
        }

        return $this;
    }

    public function removeDevisAvancement(DevisAvancement $devisAvancement): static
    {
        if ($this->devisAvancements->removeElement($devisAvancement)) {
            if ($devisAvancement->getDevis() === $this) {
                $devisAvancement->setDevis(null);
            }
        }

        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $envoyeAt = null;

    public function getEnvoyeAt(): ?\DateTimeInterface { return $this->envoyeAt; }
    public function setEnvoyeAt(?\DateTimeInterface $envoyeAt): static { $this->envoyeAt = $envoyeAt; return $this; }

    /**
     * Vérifie si le devis est validé (accepté) et peut avoir des avancements
     */
    public function isValidated(): bool
    {
        return $this->etat === 'EtatDevisAccepte';
    }

    /**
     * Compte le nombre d'avancements non supprimés
     */
    public function countActiveAvancements(): int
    {
        return $this->devisAvancements->filter(
            fn(DevisAvancement $a) => !$a->isDeleted()
        )->count();
    }
}
