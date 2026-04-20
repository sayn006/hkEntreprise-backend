<?php

namespace App\Entity;

use App\Repository\FactureSituationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: FactureSituationRepository::class)]
class FactureSituation
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\Column(type: Types::DATE_MUTABLE)]
     private ?\DateTimeInterface $dateSituation = null;

     #[ORM\Column(length: 50, nullable: true)]
     private ?string $numeroFacture = null;

     #[ORM\Column(length: 255)]
     private ?string $titre = null;

     #[ORM\ManyToOne(targetEntity: Chantier::class)]
     private ?Chantier $chantier = null;

     #[ORM\ManyToOne(targetEntity: User::class)]
     #[ORM\JoinColumn(nullable: true)]
     private ?User $createdUser = null;

     #[ORM\OneToMany(mappedBy: 'situation', targetEntity: FactureSituationFacturationTravaux::class, orphanRemoval: true)]
     private Collection $facturationTravaux;

     #[ORM\OneToMany(mappedBy: 'situation', targetEntity: FactureSituationRetenue::class, orphanRemoval: true)]
     private Collection $retenues;

     #[ORM\OneToMany(mappedBy: 'situation', targetEntity: FactureSituationTotal::class, orphanRemoval: true)]
     private Collection $totaux;

     #[ORM\OneToMany(mappedBy: 'situation', targetEntity: FactureSituationDetail::class, orphanRemoval: true)]
     private Collection $details;

     #[ORM\Column(type: 'boolean', options: ['default' => false])]
     private bool $softDelete = false;

     #[ORM\Column(nullable: true)]
     private ?float $indiceDuMois = null;

     #[ORM\Column(length: 2, nullable: true)]
     private ?string $numSituation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $montantTotalTTC = null;

     #[ORM\OneToMany(mappedBy: 'situation', targetEntity: FactureSituationPaiement::class, orphanRemoval: true)]
     private Collection $paiements;

     #[ORM\OneToMany(mappedBy: 'situation', targetEntity: FactureSituationSousTraitant::class, orphanRemoval: true)]
     private Collection $sousTraitants;

     #[ORM\OneToMany(mappedBy: 'factureSituation', targetEntity: SituationCommentaire::class, orphanRemoval: true)]
     #[ORM\OrderBy(['createdAt' => 'DESC'])]
     private Collection $commentaires;

     #[ORM\OneToMany(mappedBy: 'factureSituation', targetEntity: FactureSituationTropPercu::class, orphanRemoval: true)]
     #[ORM\OrderBy(['dateCreation' => 'DESC'])]
     private Collection $tropPercus;

     #[ORM\OneToMany(mappedBy: 'factureSituation', targetEntity: FactureSituationTropPercuUtilisation::class, orphanRemoval: true)]
     #[ORM\OrderBy(['dateUtilisation' => 'DESC'])]
     private Collection $tropPercusAppliques;

     #[ORM\OneToOne(mappedBy: 'factureSituation', targetEntity: DevisAvancement::class)]
     private ?DevisAvancement $devisAvancement = null;

     #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
     private ?\DateTimeInterface $envoyeAt = null;

     /**
      * Indique si cette facture correspond à un Décompte Général Définitif (DGD).
      * Un DGD libère la retenue de garantie cumulée à la clôture du chantier.
      */
     #[ORM\Column(type: 'boolean', options: ['default' => false])]
     private bool $isDgd = false;

     public function __construct()
     {
          $this->facturationTravaux = new ArrayCollection();
          $this->retenues = new ArrayCollection();
          $this->totaux = new ArrayCollection();
          $this->details = new ArrayCollection();
          $this->paiements = new ArrayCollection();
          $this->sousTraitants = new ArrayCollection();
          $this->commentaires = new ArrayCollection();
          $this->tropPercus = new ArrayCollection();
          $this->tropPercusAppliques = new ArrayCollection();
     }

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getDateSituation(): ?\DateTimeInterface
     {
          return $this->dateSituation;
     }

     public function setDateSituation(\DateTimeInterface $dateSituation): self
     {
          $this->dateSituation = $dateSituation;
          return $this;
     }

     public function getNumeroFacture(): ?string
     {
          return $this->numeroFacture;
     }

     public function setNumeroFacture(?string $numeroFacture): self
     {
          $this->numeroFacture = $numeroFacture;
          return $this;
     }

     public function getTitre(): ?string
     {
          return $this->titre;
     }

     public function setTitre(string $titre): self
     {
          $this->titre = $titre;
          return $this;
     }

     public function getChantier(): ?Chantier
     {
          return $this->chantier;
     }

     public function setChantier(?Chantier $chantier): self
     {
          $this->chantier = $chantier;
          return $this;
     }

     public function getCreatedUser(): ?User
     {
          return $this->createdUser;
     }

     public function setCreatedUser(?User $createdUser): self
     {
          $this->createdUser = $createdUser;
          return $this;
     }

     /**
      * @return Collection<int, FactureSituationFacturationTravaux>
      */
     public function getFacturationTravaux(): Collection
     {
          return $this->facturationTravaux;
     }

     public function addFacturationTravaux(FactureSituationFacturationTravaux $facturationTravaux): self
     {
          if (!$this->facturationTravaux->contains($facturationTravaux)) {
               $this->facturationTravaux->add($facturationTravaux);
               $facturationTravaux->setSituation($this);
          }

          return $this;
     }

     public function removeFacturationTravaux(FactureSituationFacturationTravaux $facturationTravaux): self
     {
          if ($this->facturationTravaux->removeElement($facturationTravaux)) {
               // set the owning side to null (unless already changed)
               if ($facturationTravaux->getSituation() === $this) {
                    $facturationTravaux->setSituation(null);
               }
          }

          return $this;
     }

     /**
      * @return Collection<int, FactureSituationRetenue>
      */
     public function getRetenues(): Collection
     {
          return $this->retenues;
     }

     public function addRetenue(FactureSituationRetenue $retenue): self
     {
          if (!$this->retenues->contains($retenue)) {
               $this->retenues->add($retenue);
               $retenue->setSituation($this);
          }

          return $this;
     }

     public function removeRetenue(FactureSituationRetenue $retenue): self
     {
          if ($this->retenues->removeElement($retenue)) {
               // set the owning side to null (unless already changed)
               if ($retenue->getSituation() === $this) {
                    $retenue->setSituation(null);
               }
          }

          return $this;
     }

     /**
      * @return Collection<int, FactureSituationTotal>
      */
     public function getTotaux(): Collection
     {
          return $this->totaux;
     }

     public function addTotal(FactureSituationTotal $total): self
     {
          if (!$this->totaux->contains($total)) {
               $this->totaux->add($total);
               $total->setSituation($this);
          }

          return $this;
     }

     public function removeTotal(FactureSituationTotal $total): self
     {
          if ($this->totaux->removeElement($total)) {
               // set the owning side to null (unless already changed)
               if ($total->getSituation() === $this) {
                    $total->setSituation(null);
               }
          }

          return $this;
     }

     public function isSoftDelete(): bool
     {
          return $this->softDelete;
     }

     public function setSoftDelete(bool $softDelete): self
     {
          $this->softDelete = $softDelete;
          return $this;
     }

     public function getIndiceDuMois(): ?float
     {
          return $this->indiceDuMois;
     }

     public function setIndiceDuMois(?float $indiceDuMois): self
     {
          $this->indiceDuMois = $indiceDuMois;
          return $this;
     }

     /**
      * Méthode pour récupérer les documents associés à cette situation
      * via l'entité Uploads
      */
     public function getUploads(EntityManagerInterface $entityManager): array
     {
          $uploads = $entityManager->getRepository(Uploads::class)->findBy([
               'entityId' => $this->getId(),
               'entity_type' => 'FactureSituation'
          ]);

          return $uploads;
     }

     /**
      * @return Collection<int, FactureSituationDetail>
      */
     public function getDetails(): Collection
     {
          return $this->details;
     }

     public function addDetail(FactureSituationDetail $detail): self
     {
          if (!$this->details->contains($detail)) {
               $this->details->add($detail);
               $detail->setSituation($this);
          }

          return $this;
     }

     public function removeDetail(FactureSituationDetail $detail): self
     {
          if ($this->details->removeElement($detail)) {
               // set the owning side to null (unless already changed)
               if ($detail->getSituation() === $this) {
                    $detail->setSituation(null);
               }
          }

          return $this;
     }

     public function getNumSituation(): ?string
     {
          return $this->numSituation;
     }

     public function setNumSituation(?string $numSituation): self
     {
          $this->numSituation = $numSituation;
          return $this;
     }

     public function getMontantTotalTTC(): ?string
     {
          return $this->montantTotalTTC;
     }

     public function setMontantTotalTTC(?string $montantTotalTTC): self
     {
          $this->montantTotalTTC = $montantTotalTTC;
          return $this;
     }

     /**
      * @return Collection<int, FactureSituationPaiement>
      */
     public function getPaiements(): Collection
     {
          return $this->paiements;
     }

     public function addPaiement(FactureSituationPaiement $paiement): self
     {
          if (!$this->paiements->contains($paiement)) {
               $this->paiements->add($paiement);
               $paiement->setSituation($this);
          }

          return $this;
     }

     public function removePaiement(FactureSituationPaiement $paiement): self
     {
          if ($this->paiements->removeElement($paiement)) {
               // set the owning side to null (unless already changed)
               if ($paiement->getSituation() === $this) {
                    $paiement->setSituation(null);
               }
          }

          return $this;
     }

     public function addTotaux(FactureSituationTotal $totaux): static
     {
          if (!$this->totaux->contains($totaux)) {
               $this->totaux->add($totaux);
               $totaux->setSituation($this);
          }

          return $this;
     }

     public function removeTotaux(FactureSituationTotal $totaux): static
     {
          if ($this->totaux->removeElement($totaux)) {
               // set the owning side to null (unless already changed)
               if ($totaux->getSituation() === $this) {
                    $totaux->setSituation(null);
               }
          }

          return $this;
     }

     /**
      * @return Collection<int, FactureSituationSousTraitant>
      */
     public function getSousTraitants(): Collection
     {
          return $this->sousTraitants;
     }

     public function addSousTraitant(FactureSituationSousTraitant $sousTraitant): static
     {
          if (!$this->sousTraitants->contains($sousTraitant)) {
               $this->sousTraitants->add($sousTraitant);
               $sousTraitant->setSituation($this);
          }

          return $this;
     }

     public function removeSousTraitant(FactureSituationSousTraitant $sousTraitant): static
     {
          if ($this->sousTraitants->removeElement($sousTraitant)) {
               // set the owning side to null (unless already changed)
               if ($sousTraitant->getSituation() === $this) {
                    $sousTraitant->setSituation(null);
               }
          }

          return $this;
     }

     /**
      * @return Collection<int, SituationCommentaire>
      */
     public function getCommentaires(): Collection
     {
          return $this->commentaires;
     }

     public function addCommentaire(SituationCommentaire $commentaire): static
     {
          if (!$this->commentaires->contains($commentaire)) {
               $this->commentaires->add($commentaire);
               $commentaire->setFactureSituation($this);
          }

          return $this;
     }

     public function removeCommentaire(SituationCommentaire $commentaire): static
     {
          if ($this->commentaires->removeElement($commentaire)) {
               // set the owning side to null (unless already changed)
               if ($commentaire->getFactureSituation() === $this) {
                    $commentaire->setFactureSituation(null);
               }
          }

          return $this;
     }

     /**
      * @return Collection<int, FactureSituationTropPercu>
      */
     public function getTropPercus(): Collection
     {
          return $this->tropPercus;
     }

     public function addTropPercu(FactureSituationTropPercu $tropPercu): static
     {
          if (!$this->tropPercus->contains($tropPercu)) {
               $this->tropPercus->add($tropPercu);
               $tropPercu->setFactureSituation($this);
          }

          return $this;
     }

     public function removeTropPercu(FactureSituationTropPercu $tropPercu): static
     {
          if ($this->tropPercus->removeElement($tropPercu)) {
               // set the owning side to null (unless already changed)
               if ($tropPercu->getFactureSituation() === $this) {
                    $tropPercu->setFactureSituation(null);
               }
          }

          return $this;
     }

     /**
      * @return Collection<int, FactureSituationTropPercuUtilisation>
      */
     public function getTropPercusAppliques(): Collection
     {
          return $this->tropPercusAppliques;
     }

     public function addTropPercusApplique(FactureSituationTropPercuUtilisation $tropPercusApplique): static
     {
          if (!$this->tropPercusAppliques->contains($tropPercusApplique)) {
               $this->tropPercusAppliques->add($tropPercusApplique);
               $tropPercusApplique->setFactureSituation($this);
          }

          return $this;
     }

     public function removeTropPercusApplique(FactureSituationTropPercuUtilisation $tropPercusApplique): static
     {
          if ($this->tropPercusAppliques->removeElement($tropPercusApplique)) {
               // set the owning side to null (unless already changed)
               if ($tropPercusApplique->getFactureSituation() === $this) {
                    $tropPercusApplique->setFactureSituation(null);
               }
          }

          return $this;
     }

     /**
      * Calcule le total des trop-perçus appliqués sur cette facture
      */
     public function getTotalTropPercusAppliques(): float
     {
          $total = 0;
          foreach ($this->tropPercusAppliques as $utilisation) {
               $total += (float)$utilisation->getMontant();
          }
          return $total;
     }

     public function getEnvoyeAt(): ?\DateTimeInterface
     {
          return $this->envoyeAt;
     }

     public function setEnvoyeAt(?\DateTimeInterface $envoyeAt): self
     {
          $this->envoyeAt = $envoyeAt;
          return $this;
     }

     public function isDgd(): bool
     {
          return $this->isDgd;
     }

     public function setIsDgd(bool $isDgd): self
     {
          $this->isDgd = $isDgd;
          return $this;
     }

     public function getDevisAvancement(): ?DevisAvancement
     {
          return $this->devisAvancement;
     }

     public function setDevisAvancement(?DevisAvancement $devisAvancement): self
     {
          $this->devisAvancement = $devisAvancement;
          return $this;
     }
}
