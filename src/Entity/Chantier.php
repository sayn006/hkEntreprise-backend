<?php

namespace App\Entity;

use App\Repository\ChantierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: ChantierRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['chantier:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['chantier:write']],
)]
class Chantier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chantier:read', 'facture:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?bool $softDelete = false;

    #[ORM\Column(length: 25)]
    #[Groups(['chantier:read', 'chantier:write', 'devis:read', 'devis_avancement:read', 'facture:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['chantier:read', 'chantier:write', 'devis:read', 'devis_avancement:read', 'facture:read'])]
    private ?string $nom = null;

    #[ORM\ManyToOne]
    private ?Region $region = null;

    #[ORM\ManyToOne]
    private ?TypeLibelles $typeCalcul = null;

    /**
     * @var Collection<int, ChantierSousTraitent>
     */
    #[ORM\OneToMany(mappedBy: 'chantier', targetEntity: ChantierSousTraitent::class, orphanRemoval: true)]
    private Collection $chantierSousTraitents;

    #[ORM\Column(length: 255)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $moe_nom_prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $moe_email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $adresse_chantier = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $montant_contrat_ht = '0.00';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $duree_chantier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nature_prestation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nature_operation = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $cp_chantier = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chantier:read', 'chantier:write'])]
    private ?string $ville_chantier = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateSignatureContrat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $villeSignatureContrat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objetMarcher = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $adresseChantier2 = null;


    /**
     * @var Collection<int, BonCommande>
     */
    #[ORM\OneToMany(targetEntity: BonCommande::class, mappedBy: 'chantier', orphanRemoval: true)]
    private Collection $bonCommandes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactChantier = null;

    /**
     * @var Collection<int, SuiviFacture>
     */
    #[ORM\OneToMany(targetEntity: SuiviFacture::class, mappedBy: 'chantier')]
    private Collection $suiviFactures;

    #[ORM\ManyToOne(inversedBy: 'chantiers')]
    #[Groups(['chantier:read', 'chantier:write'])]
    #[MaxDepth(1)]
    private ?Client $client = null;

    #[ORM\Column(nullable: true)]
    private ?float $prorata_percent = null;

    #[ORM\Column(nullable: true)]
    private ?float $rg_ht = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $moeTel = null;

    /**
     * @var Collection<int, ChantierResponsable>
     */
    #[ORM\OneToMany(targetEntity: ChantierResponsable::class, mappedBy: 'chantier', cascade: ['persist'])]
    private Collection $chantierResponsables;

    /**
     * @var Collection<int, FactureSituationCommentaire>
     */
    #[ORM\OneToMany(targetEntity: FactureSituationCommentaire::class, mappedBy: 'chantier', orphanRemoval: true)]
    private Collection $factureSituationCommentaires;

    #[ORM\Column(nullable: true)]
    private ?float $revisionPrix = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $pourcentageRetenue = '5.00';

    /**
     * Latitude GPS du chantier pour vérification géolocalisation
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    /**
     * Longitude GPS du chantier pour vérification géolocalisation
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    /**
     * Rayon de tolérance en mètres pour le badgeage (geofencing)
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 100])]
    private ?int $geofenceRadius = 100;

    public function __construct()
    {
        $this->chantierSousTraitents = new ArrayCollection();
        $this->bonCommandes = new ArrayCollection();
        $this->suiviFactures = new ArrayCollection();
        $this->chantierResponsables = new ArrayCollection();
        $this->factureSituationCommentaires = new ArrayCollection();
        $this->softDelete = false;
    }

    public function __toString()
    {
        return $this->code . ' - ' . $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoftDelete(): ?bool
    {
        return $this->softDelete;
    }

    public function setSoftDelete(bool $softDelete): static
    {
        $this->softDelete = $softDelete;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getTypeCalcul(): ?TypeLibelles
    {
        return $this->typeCalcul;
    }

    public function setTypeCalcul(?TypeLibelles $typeCalcul): static
    {
        $this->typeCalcul = $typeCalcul;

        return $this;
    }

    /**
     * @return Collection<int, ChantierSousTraitent>
     */
    public function getChantierSousTraitents(): Collection
    {
        return $this->chantierSousTraitents;
    }

    public function addChantierSousTraitent(ChantierSousTraitent $chantierSousTraitent): self
    {
        if (!$this->chantierSousTraitents->contains($chantierSousTraitent)) {
            $this->chantierSousTraitents->add($chantierSousTraitent);
            $chantierSousTraitent->setChantier($this);
        }

        return $this;
    }

    public function removeChantierSousTraitent(ChantierSousTraitent $chantierSousTraitent): self
    {
        if ($this->chantierSousTraitents->removeElement($chantierSousTraitent)) {
            // set the owning side to null (unless already changed)
            if ($chantierSousTraitent->getChantier() === $this) {
                $chantierSousTraitent->setChantier(null);
            }
        }

        return $this;
    }

    /**
     * Méthode utilitaire pour obtenir tous les sous-traitants associés à ce chantier
     * @return Collection<int, SousTraitent>
     */
    public function getSousTraitents(): Collection
    {
        $sousTraitents = new ArrayCollection();
        foreach ($this->chantierSousTraitents as $relation) {
            $sousTraitents->add($relation->getSousTraitent());
        }
        return $sousTraitents;
    }

    /**
     * Méthode utilitaire pour ajouter un sous-traitant à ce chantier
     */
    public function addSousTraitent(SousTraitent $sousTraitent): self
    {
        // Vérifier si la relation existe déjà
        foreach ($this->chantierSousTraitents as $relation) {
            if ($relation->getSousTraitent() === $sousTraitent) {
                return $this;
            }
        }

        // Créer une nouvelle relation
        $relation = new ChantierSousTraitent();
        $relation->setChantier($this);
        $relation->setSousTraitent($sousTraitent);
        $this->chantierSousTraitents->add($relation);

        return $this;
    }

    /**
     * Méthode utilitaire pour supprimer un sous-traitant de ce chantier
     */
    public function removeSousTraitent(SousTraitent $sousTraitent): self
    {
        foreach ($this->chantierSousTraitents as $relation) {
            if ($relation->getSousTraitent() === $sousTraitent) {
                $this->chantierSousTraitents->removeElement($relation);
                break;
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getMoeNomPrenom(): ?string
    {
        return $this->moe_nom_prenom;
    }

    public function setMoeNomPrenom(?string $moe_nom_prenom): static
    {
        $this->moe_nom_prenom = $moe_nom_prenom;

        return $this;
    }

    public function getMoeEmail(): ?string
    {
        return $this->moe_email;
    }

    public function setMoeEmail(?string $moe_email): static
    {
        $this->moe_email = $moe_email;

        return $this;
    }

    public function getAdresseChantier(): ?string
    {
        return $this->adresse_chantier;
    }

    public function setAdresseChantier(?string $adresse_chantier): static
    {
        $this->adresse_chantier = $adresse_chantier;

        return $this;
    }

    public function getMontantContratHt(): string
    {
        return $this->montant_contrat_ht ?? '0.00';
    }

    public function setMontantContratHt(?string $montant_contrat_ht): self
    {
        $this->montant_contrat_ht = $montant_contrat_ht ?: '0.00';
        return $this;
    }

    public function getDureeChantier(): ?string
    {
        return $this->duree_chantier;
    }

    public function setDureeChantier(?string $duree_chantier): static
    {
        $this->duree_chantier = $duree_chantier;

        return $this;
    }

    public function getNaturePrestation(): ?string
    {
        return $this->nature_prestation;
    }

    public function setNaturePrestation(?string $nature_prestation): static
    {
        $this->nature_prestation = $nature_prestation;

        return $this;
    }

    public function getNatureOperation(): ?string
    {
        return $this->nature_operation;
    }

    public function setNatureOperation(?string $nature_operation): static
    {
        $this->nature_operation = $nature_operation;

        return $this;
    }

    public function getCpChantier(): ?string
    {
        return $this->cp_chantier;
    }

    public function setCpChantier(?string $cp_chantier): static
    {
        $this->cp_chantier = $cp_chantier;

        return $this;
    }

    public function getVilleChantier(): ?string
    {
        return $this->ville_chantier;
    }

    public function setVilleChantier(?string $ville_chantier): static
    {
        $this->ville_chantier = $ville_chantier;

        return $this;
    }

    public function getDateSignatureContrat(): ?\DateTimeInterface
    {
        return $this->dateSignatureContrat;
    }

    public function setDateSignatureContrat(?\DateTimeInterface $dateSignatureContrat): static
    {
        $this->dateSignatureContrat = $dateSignatureContrat;

        return $this;
    }

    public function getVilleSignatureContrat(): ?string
    {
        return $this->villeSignatureContrat;
    }

    public function setVilleSignatureContrat(?string $villeSignatureContrat): static
    {
        $this->villeSignatureContrat = $villeSignatureContrat;

        return $this;
    }

    public function getObjetMarcher(): ?string
    {
        return $this->objetMarcher;
    }

    public function setObjetMarcher(?string $objetMarcher): static
    {
        $this->objetMarcher = $objetMarcher;

        return $this;
    }

    public function getAdresseChantier2(): ?string
    {
        return $this->adresseChantier2;
    }

    public function setAdresseChantier2(?string $adresseChantier2): static
    {
        $this->adresseChantier2 = $adresseChantier2;

        return $this;
    }


    /**
     * @return Collection<int, BonCommande>
     */
    public function getBonCommandes(): Collection
    {
        return $this->bonCommandes;
    }

    public function addBonCommande(BonCommande $bonCommande): static
    {
        if (!$this->bonCommandes->contains($bonCommande)) {
            $this->bonCommandes->add($bonCommande);
            $bonCommande->setChantier($this);
        }

        return $this;
    }

    public function removeBonCommande(BonCommande $bonCommande): static
    {
        if ($this->bonCommandes->removeElement($bonCommande)) {
            // set the owning side to null (unless already changed)
            if ($bonCommande->getChantier() === $this) {
                $bonCommande->setChantier(null);
            }
        }

        return $this;
    }

    public function getContactChantier(): ?string
    {
        return $this->contactChantier;
    }

    public function setContactChantier(?string $contactChantier): static
    {
        $this->contactChantier = $contactChantier;

        return $this;
    }

    /**
     * @return Collection<int, SuiviFacture>
     */
    public function getSuiviFactures(): Collection
    {
        return $this->suiviFactures;
    }

    public function addSuiviFacture(SuiviFacture $suiviFacture): static
    {
        if (!$this->suiviFactures->contains($suiviFacture)) {
            $this->suiviFactures->add($suiviFacture);
            $suiviFacture->setChantier($this);
        }

        return $this;
    }

    public function removeSuiviFacture(SuiviFacture $suiviFacture): static
    {
        if ($this->suiviFactures->removeElement($suiviFacture)) {
            // set the owning side to null (unless already changed)
            if ($suiviFacture->getChantier() === $this) {
                $suiviFacture->setChantier(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getProrataPercent(): ?float
    {
        return $this->prorata_percent;
    }

    public function setProrataPercent(?float $prorata_percent): static
    {
        $this->prorata_percent = $prorata_percent;

        return $this;
    }

    public function getRgHt(): ?float
    {
        return $this->rg_ht;
    }

    public function setRgHt(?float $rg_ht): static
    {
        $this->rg_ht = $rg_ht;

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

    public function getMoeTel(): ?string
    {
        return $this->moeTel;
    }

    public function setMoeTel(?string $moeTel): static
    {
        $this->moeTel = $moeTel;

        return $this;
    }

    /**
     * @return Collection<int, ChantierResponsable>
     */
    public function getChantierResponsables(): Collection
    {
        return $this->chantierResponsables;
    }

    public function addChantierResponsable(ChantierResponsable $chantierResponsable): static
    {
        if (!$this->chantierResponsables->contains($chantierResponsable)) {
            $this->chantierResponsables->add($chantierResponsable);
            $chantierResponsable->setChantier($this);
        }

        return $this;
    }

    public function removeChantierResponsable(ChantierResponsable $chantierResponsable): static
    {
        if ($this->chantierResponsables->removeElement($chantierResponsable)) {
            if ($chantierResponsable->getChantier() === $this) {
                $chantierResponsable->setChantier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getResponsables(): Collection
    {
        $responsables = new ArrayCollection();
        foreach ($this->chantierResponsables as $chantierResponsable) {
            $responsables->add($chantierResponsable->getResponsable());
        }
        return $responsables;
    }

    public function addResponsable(User $user): static
    {
        $chantierResponsable = new ChantierResponsable();
        $chantierResponsable->setResponsable($user);
        $chantierResponsable->setChantier($this);
        $this->chantierResponsables->add($chantierResponsable);

        return $this;
    }

    public function removeResponsable(User $user): static
    {
        foreach ($this->chantierResponsables as $chantierResponsable) {
            if ($chantierResponsable->getResponsable() === $user) {
                $this->chantierResponsables->removeElement($chantierResponsable);
                break;
            }
        }

        return $this;
    }

    public function getRevisionPrix(): ?float
    {
        return $this->revisionPrix;
    }

    public function setRevisionPrix(?float $revisionPrix): static
    {
        $this->revisionPrix = $revisionPrix;

        return $this;
    }

    public function getPourcentageRetenue(): ?float
    {
        return $this->pourcentageRetenue;
    }

    public function setPourcentageRetenue(?float $pourcentageRetenue): self
    {
        $this->pourcentageRetenue = $pourcentageRetenue;
        return $this;
    }

    // ============================================================================
    // GEOLOCALISATION GPS - Badgeage
    // ============================================================================

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getGeofenceRadius(): ?int
    {
        return $this->geofenceRadius;
    }

    public function setGeofenceRadius(?int $geofenceRadius): static
    {
        $this->geofenceRadius = $geofenceRadius;
        return $this;
    }

    /**
     * Vérifie si le chantier a une position GPS définie
     */
    public function hasGpsLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Obtenir le badge HTML de status GPS (pour affichage dans les templates)
     */
    public function getGpsStatusBadge(): string
    {
        if ($this->hasGpsLocation()) {
            return '<span class="badge badge-success" title="Lat: ' . $this->latitude . ', Lon: ' . $this->longitude . '"><i class="fas fa-check-circle"></i> GPS Défini</span>';
        }
        return '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> GPS Absent</span>';
    }

    /**
     * Obtenir le texte du status GPS (pour API JSON)
     */
    public function getGpsStatus(): string
    {
        return $this->hasGpsLocation() ? 'defined' : 'undefined';
    }

    /**
     * Obtenir les coordonnées GPS sous forme de tableau (pour API JSON)
     */
    public function getGpsCoordinates(): ?array
    {
        if (!$this->hasGpsLocation()) {
            return null;
        }

        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'radius' => $this->geofenceRadius ?? 100,
        ];
    }

    /**
     * @return Collection<int, FactureSituationCommentaire>
     */
    public function getFactureSituationCommentaires(): Collection
    {
        return $this->factureSituationCommentaires;
    }

    public function addFactureSituationCommentaire(FactureSituationCommentaire $commentaire): static
    {
        if (!$this->factureSituationCommentaires->contains($commentaire)) {
            $this->factureSituationCommentaires->add($commentaire);
            $commentaire->setChantier($this);
        }

        return $this;
    }

    public function removeFactureSituationCommentaire(FactureSituationCommentaire $commentaire): static
    {
        if ($this->factureSituationCommentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getChantier() === $this) {
                $commentaire->setChantier(null);
            }
        }

        return $this;
    }
}
