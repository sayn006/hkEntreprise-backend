<?php

namespace App\Entity;

use App\Repository\SousTraitentRepository;
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

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['sous_traitant:read']],
    denormalizationContext: ['groups' => ['sous_traitant:write']],
)]
#[ORM\Entity(repositoryClass: SousTraitentRepository::class)]
class SousTraitent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['sous_traitant:read'])]
    private ?int $id = null;

    #[ORM\Column(name: 'raison_social')]
    #[Groups(['sous_traitant:read', 'sous_traitant:write'])]
    private ?string $raisonSocial = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['sous_traitant:read', 'sous_traitant:write'])]
    private ?string $nom_gerant = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['sous_traitant:read', 'sous_traitant:write'])]
    private ?string $prenom_gerant = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['sous_traitant:read', 'sous_traitant:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Groups(['sous_traitant:read', 'sous_traitant:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $siren = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $updatedUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    /**
     * @var Collection<int, ChantierSousTraitent>
     */
    #[ORM\OneToMany(mappedBy: 'sousTraitent', targetEntity: ChantierSousTraitent::class, orphanRemoval: true)]
    private Collection $chantierSousTraitents;

    /**
     * @var Collection<int, SousTraitantContrat>
     */
    #[ORM\OneToMany(mappedBy: 'sousTraitant', targetEntity: SousTraitantContrat::class, orphanRemoval: true)]
    private Collection $contrats;

    #[ORM\ManyToOne]
    private ?FormeJuridique $forme_juridique = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code_postal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rcs = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $capital = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $numero_tva = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $code_naf_ape = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date_immat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rib = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $banque_adresse = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ex1DateDU = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ex1DateAu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ex2DateDu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ex2DateAu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ex3DateDu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ex3DateAu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $ex1Ca = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $ex2Ca = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $ex3Ca = null;

    public function __construct()
    {
        $this->chantierSousTraitents = new ArrayCollection();
        $this->contrats = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->raisonSocial;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSocial(): ?string
    {
        return $this->raisonSocial;
    }

    public function setRaisonSocial(string $raisonSocial): static
    {
        $this->raisonSocial = $raisonSocial;

        return $this;
    }


    public function getNomGerant(): ?string
    {
        return $this->nom_gerant;
    }

    public function setNomGerant(?string $nom_gerant): static
    {
        $this->nom_gerant = $nom_gerant;

        return $this;
    }

    public function getPrenomGerant(): ?string
    {
        return $this->prenom_gerant;
    }

    public function setPrenomGerant(?string $prenom_gerant): static
    {
        $this->prenom_gerant = $prenom_gerant;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
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

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren(?string $siren): static
    {
        $this->siren = $siren;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

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

    public function getUpdatedUser(): ?string
    {
        return $this->updatedUser;
    }

    public function setUpdatedUser(?string $updatedUser): static
    {
        $this->updatedUser = $updatedUser;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getFormeJuridique(): ?FormeJuridique
    {
        return $this->forme_juridique;
    }

    public function setFormeJuridique(?FormeJuridique $forme_juridique): static
    {
        $this->forme_juridique = $forme_juridique;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(?string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getRcs(): ?string
    {
        return $this->rcs;
    }

    public function setRcs(?string $rcs): static
    {
        $this->rcs = $rcs;

        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(?string $capital): static
    {
        $this->capital = $capital;

        return $this;
    }

    public function getNumeroTva(): ?string
    {
        return $this->numero_tva;
    }

    public function setNumeroTva(?string $numero_tva): static
    {
        $this->numero_tva = $numero_tva;

        return $this;
    }

    public function getCodeNafApe(): ?string
    {
        return $this->code_naf_ape;
    }

    public function setCodeNafApe(?string $code_naf_ape): static
    {
        $this->code_naf_ape = $code_naf_ape;

        return $this;
    }

    public function getDateImmat(): ?string
    {
        return $this->date_immat;
    }

    public function setDateImmat(?string $date_immat): static
    {
        $this->date_immat = $date_immat;

        return $this;
    }

    public function getRib(): ?string
    {
        return $this->rib;
    }

    public function setRib(?string $rib): static
    {
        $this->rib = $rib;

        return $this;
    }

    public function getBanqueAdresse(): ?string
    {
        return $this->banque_adresse;
    }

    public function setBanqueAdresse(?string $banque_adresse): static
    {
        $this->banque_adresse = $banque_adresse;

        return $this;
    }

    public function getEx1DateDU(): ?\DateTimeInterface
    {
        return $this->ex1DateDU;
    }

    public function setEx1DateDU(?\DateTimeInterface $ex1DateDU): static
    {
        $this->ex1DateDU = $ex1DateDU;

        return $this;
    }

    public function getEx1DateAu(): ?\DateTimeInterface
    {
        return $this->ex1DateAu;
    }

    public function setEx1DateAu(?\DateTimeInterface $ex1DateAu): static
    {
        $this->ex1DateAu = $ex1DateAu;

        return $this;
    }

    public function getEx2DateDu(): ?\DateTimeInterface
    {
        return $this->ex2DateDu;
    }

    public function setEx2DateDu(?\DateTimeInterface $ex2DateDu): static
    {
        $this->ex2DateDu = $ex2DateDu;

        return $this;
    }

    public function getEx2DateAu(): ?\DateTimeInterface
    {
        return $this->ex2DateAu;
    }

    public function setEx2DateAu(?\DateTimeInterface $ex2DateAu): static
    {
        $this->ex2DateAu = $ex2DateAu;

        return $this;
    }

    public function getEx3DateDu(): ?\DateTimeInterface
    {
        return $this->ex3DateDu;
    }

    public function setEx3DateDu(?\DateTimeInterface $ex3DateDu): static
    {
        $this->ex3DateDu = $ex3DateDu;

        return $this;
    }

    public function getEx3DateAu(): ?\DateTimeInterface
    {
        return $this->ex3DateAu;
    }

    public function setEx3DateAu(?\DateTimeInterface $ex3DateAu): static
    {
        $this->ex3DateAu = $ex3DateAu;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getEx1Ca(): ?string
    {
        return $this->ex1Ca;
    }

    public function setEx1Ca(?string $ex1Ca): static
    {
        $this->ex1Ca = $ex1Ca;

        return $this;
    }

    public function getEx2Ca(): ?string
    {
        return $this->ex2Ca;
    }

    public function setEx2Ca(?string $ex2Ca): static
    {
        $this->ex2Ca = $ex2Ca;

        return $this;
    }

    public function getEx3Ca(): ?string
    {
        return $this->ex3Ca;
    }

    public function setEx3Ca(?string $ex3Ca): static
    {
        $this->ex3Ca = $ex3Ca;

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
            $chantierSousTraitent->setSousTraitent($this);
        }

        return $this;
    }

    public function removeChantierSousTraitent(ChantierSousTraitent $chantierSousTraitent): self
    {
        if ($this->chantierSousTraitents->removeElement($chantierSousTraitent)) {
            // set the owning side to null (unless already changed)
            if ($chantierSousTraitent->getSousTraitent() === $this) {
                $chantierSousTraitent->setSousTraitent(null);
            }
        }

        return $this;
    }

    /**
     * Méthode utilitaire pour obtenir tous les chantiers associés à ce sous-traitant
     * @return Collection<int, Chantier>
     */
    public function getChantiers(): Collection
    {
        $chantiers = new ArrayCollection();
        foreach ($this->chantierSousTraitents as $relation) {
            $chantiers->add($relation->getChantier());
        }
        return $chantiers;
    }

    /**
     * Méthode utilitaire pour ajouter un chantier à ce sous-traitant
     */
    public function addChantier(Chantier $chantier): self
    {
        // Vérifier si la relation existe déjà
        foreach ($this->chantierSousTraitents as $relation) {
            if ($relation->getChantier() === $chantier) {
                return $this;
            }
        }

        // Créer une nouvelle relation
        $relation = new ChantierSousTraitent();
        $relation->setSousTraitent($this);
        $relation->setChantier($chantier);
        $this->chantierSousTraitents->add($relation);

        return $this;
    }

    /**
     * Méthode utilitaire pour supprimer un chantier de ce sous-traitant
     */
    public function removeChantier(Chantier $chantier): self
    {
        foreach ($this->chantierSousTraitents as $relation) {
            if ($relation->getChantier() === $chantier) {
                $this->chantierSousTraitents->removeElement($relation);
                break;
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SousTraitantContrat>
     */
    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(SousTraitantContrat $contrat): self
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->setSousTraitant($this);
        }

        return $this;
    }

    public function removeContrat(SousTraitantContrat $contrat): self
    {
        if ($this->contrats->removeElement($contrat)) {
            // set the owning side to null (unless already changed)
            if ($contrat->getSousTraitant() === $this) {
                $contrat->setSousTraitant(null);
            }
        }

        return $this;
    }
}
