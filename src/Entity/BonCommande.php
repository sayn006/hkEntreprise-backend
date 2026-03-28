<?php

namespace App\Entity;

use App\Repository\BonCommandeRepository;
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
    normalizationContext: ['groups' => ['bon_commande:read']],
    denormalizationContext: ['groups' => ['bon_commande:write']],
)]
#[ORM\Entity(repositoryClass: BonCommandeRepository::class)]
class BonCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bon_commande:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chantier $chantier = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validateAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createUser = null;

    #[ORM\ManyToOne]
    private ?User $validateUser = null;

    #[ORM\Column(length: 100)]
    #[Groups(['bon_commande:read', 'bon_commande:write'])]
    private ?string $numCommande = null;

    /**
     * @var Collection<int, BonCommandeArticle>
     */
    #[ORM\OneToMany(targetEntity: BonCommandeArticle::class, mappedBy: 'bonCommande')]
    private Collection $bonCommandeArticles;

    #[ORM\ManyToOne(inversedBy: 'bonCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fournisseur $fournisseur = null;

    #[ORM\Column(nullable: true)]
    private ?int $isFinished = null;

    #[ORM\Column(nullable: true)]
    private ?int $isValidated = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichier = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['bon_commande:read', 'bon_commande:write'])]
    private ?float $montantTotal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(nullable: true)]
    private ?int $isSent = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandes')]
    private ?FournisseurContact $fournisseurContact = null;

    #[ORM\Column(nullable: true)]
    private ?int $isDelivered = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deliveredAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $historique = null;

    #[ORM\ManyToOne]
    private ?Contacts $sentFounisseurContact = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLivraisonFournisseur = null;

    public function __construct()
    {
        $this->bonCommandeArticles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChantier(): ?Chantier
    {
        return $this->chantier;
    }

    public function setChantier(?Chantier $chantier): static
    {
        $this->chantier = $chantier;

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

    public function getValidateAt(): ?\DateTimeImmutable
    {
        return $this->validateAt;
    }

    public function setValidateAt(?\DateTimeImmutable $validateAt): static
    {
        $this->validateAt = $validateAt;

        return $this;
    }

    public function getCreateUser(): ?User
    {
        return $this->createUser;
    }

    public function setCreateUser(?User $createUser): static
    {
        $this->createUser = $createUser;

        return $this;
    }

    public function getValidateUser(): ?User
    {
        return $this->validateUser;
    }

    public function setValidateUser(?User $validateUser): static
    {
        $this->validateUser = $validateUser;

        return $this;
    }

    public function getNumCommande(): ?string
    {
        return $this->numCommande;
    }

    public function setNumCommande(string $numCommande): static
    {
        $this->numCommande = $numCommande;

        return $this;
    }

    /**
     * @return Collection<int, BonCommandeArticle>
     */
    public function getBonCommandeArticles(): Collection
    {
        return $this->bonCommandeArticles;
    }

    public function addBonCommandeArticle(BonCommandeArticle $bonCommandeArticle): static
    {
        if (!$this->bonCommandeArticles->contains($bonCommandeArticle)) {
            $this->bonCommandeArticles->add($bonCommandeArticle);
            $bonCommandeArticle->setBonCommande($this);
        }

        return $this;
    }

    public function removeBonCommandeArticle(BonCommandeArticle $bonCommandeArticle): static
    {
        if ($this->bonCommandeArticles->removeElement($bonCommandeArticle)) {
            // set the owning side to null (unless already changed)
            if ($bonCommandeArticle->getBonCommande() === $this) {
                $bonCommandeArticle->setBonCommande(null);
            }
        }

        return $this;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }


    public function getIsFinished(): ?int
    {
        return $this->isFinished;
    }

    public function setIsFinished(?int $isFinished): static
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    public function getIsValidated(): ?int
    {
        return $this->isValidated;
    }

    public function setIsValidated(?int $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montantTotal;
    }

    public function setMontantTotal(?float $montantTotal): static
    {
        $this->montantTotal = $montantTotal;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getIsSent(): ?int
    {
        return $this->isSent;
    }

    public function setIsSent(?int $isSent): static
    {
        $this->isSent = $isSent;

        return $this;
    }

    public function getFournisseurContact(): ?FournisseurContact
    {
        return $this->fournisseurContact;
    }

    public function setFournisseurContact(?FournisseurContact $fournisseurContact): static
    {
        $this->fournisseurContact = $fournisseurContact;

        return $this;
    }

    public function getIsDelivered(): ?int
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(?int $isDelivered): static
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeInterface $deliveredAt): static
    {
        $this->deliveredAt = $deliveredAt;

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

    public function getHistorique(): ?string
    {
        return $this->historique;
    }

    public function setHistorique(?string $historique): static
    {
        $this->historique = $historique;

        return $this;
    }

    public function getSentFounisseurContact(): ?Contacts
    {
        return $this->sentFounisseurContact;
    }

    public function setSentFounisseurContact(?Contacts $sentFounisseurContact): static
    {
        $this->sentFounisseurContact = $sentFounisseurContact;

        return $this;
    }

    public function getDateLivraisonFournisseur(): ?\DateTimeInterface
    {
        return $this->dateLivraisonFournisseur;
    }

    public function setDateLivraisonFournisseur(?\DateTimeInterface $dateLivraisonFournisseur): static
    {
        $this->dateLivraisonFournisseur = $dateLivraisonFournisseur;

        return $this;
    }
}
