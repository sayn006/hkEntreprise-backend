<?php

namespace App\Entity;

use App\Repository\FournisseurContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FournisseurContactRepository::class)]
class FournisseurContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fournisseurContacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fournisseur $fournisseur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    /**
     * @var Collection<int, BonCommande>
     */
    #[ORM\OneToMany(targetEntity: BonCommande::class, mappedBy: 'fournisseurContact')]
    private Collection $bonCommandes;

    public function __construct()
    {
        $this->bonCommandes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->nom.' '.$this->prenom.' : '.$this->email;

    }
    public function getId(): ?int
    {
        return $this->id;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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
            $bonCommande->setFournisseurContact($this);
        }

        return $this;
    }

    public function removeBonCommande(BonCommande $bonCommande): static
    {
        if ($this->bonCommandes->removeElement($bonCommande)) {
            // set the owning side to null (unless already changed)
            if ($bonCommande->getFournisseurContact() === $this) {
                $bonCommande->setFournisseurContact(null);
            }
        }

        return $this;
    }
}
