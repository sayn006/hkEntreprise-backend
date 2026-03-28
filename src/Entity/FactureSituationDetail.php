<?php

namespace App\Entity;

use App\Entity\Chantier;
use App\Entity\SousTraitent;
use App\Repository\FactureSituationDetailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureSituationDetailRepository::class)]
class FactureSituationDetail
{
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column]
     private ?int $id = null;

     #[ORM\ManyToOne(inversedBy: 'details')]
     #[ORM\JoinColumn(nullable: false)]
     private ?FactureSituation $situation = null;

     #[ORM\Column(length: 255)]
     private ?string $designation = null;

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $montant = '0.00';

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $cumule = '0.00';

     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $cumuleAnterieur = '0.00';

     #[ORM\Column(length: 50, nullable: true)]
     private ?string $type = null;

     #[ORM\Column(length: 50)]
     private ?string $groupe = null;

     #[ORM\ManyToOne]
     #[ORM\JoinColumn(nullable: true)]
     private ?Chantier $chantier = null;

     #[ORM\ManyToOne]
     #[ORM\JoinColumn(nullable: true)]
     private ?SousTraitent $sousTraitent = null;

     // Nouvelles propriétés pour les paiements
     #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
     private ?string $montantPaye = null;

     #[ORM\Column(length: 255, nullable: true)]
     private ?string $statusPaiement = null;

     #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
     private ?\DateTimeInterface $datePaiement = null;

     public function __construct()
     {
          $this->montant = '0.00';
          $this->cumule = '0.00';
          $this->cumuleAnterieur = '0.00';
     }

     public function getId(): ?int
     {
          return $this->id;
     }

     public function getSituation(): ?FactureSituation
     {
          return $this->situation;
     }

     public function setSituation(?FactureSituation $situation): self
     {
          $this->situation = $situation;
          return $this;
     }

     public function getDesignation(): ?string
     {
          return $this->designation;
     }

     public function setDesignation(string $designation): self
     {
          $this->designation = $designation;
          return $this;
     }

     public function getMontant(): ?string
     {
          return $this->montant;
     }

     public function setMontant(?string $montant): self
     {
          $this->montant = $montant;
          return $this;
     }

     public function getCumule(): ?string
     {
          return $this->cumule;
     }

     public function setCumule(?string $cumule): self
     {
          $this->cumule = $cumule;
          return $this;
     }

     public function getCumuleAnterieur(): ?string
     {
          return $this->cumuleAnterieur;
     }

     public function setCumuleAnterieur(?string $cumuleAnterieur): self
     {
          $this->cumuleAnterieur = $cumuleAnterieur;
          return $this;
     }

     public function getType(): ?string
     {
          return $this->type;
     }

     public function setType(?string $type): self
     {
          $this->type = $type;
          if ($type === 'marche' && $this->situation) {
               $this->setChantier($this->situation->getChantier());
          }
          return $this;
     }

     public function getGroupe(): ?string
     {
          return $this->groupe;
     }

     public function setGroupe(string $groupe): self
     {
          $this->groupe = $groupe;
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

     public function getSousTraitent(): ?SousTraitent
     {
          return $this->sousTraitent;
     }

     public function setSousTraitent(?SousTraitent $sousTraitent): self
     {
          $this->sousTraitent = $sousTraitent;
          if ($sousTraitent && $this->type !== self::TYPE_ST_NON_DECLARE) {
               $this->setType(self::TYPE_ST);
          }
          return $this;
     }

     // Constantes pour les groupes
     public const GROUPE_FACTURATION_TRAVAUX = 'FACTURATION_TRAVAUX';
     public const GROUPE_RETENUES = 'RETENUES';
     public const GROUPE_TOTAUX = 'TOTAUX';
     public const GROUPE_REVISION = 'REVISION';
     public const GROUPE_DETAIL_SOUS_TRAITANTS = 'DETAIL_SOUS_TRAITANTS';

     // Constantes pour les types
     public const TYPE_MARCHE = 'marche';
     public const TYPE_ST = 'st';
     public const TYPE_ST_NON_DECLARE = 'stNonDeclare';

     public function getMontantPaye(): ?string
     {
          return $this->montantPaye;
     }

     public function setMontantPaye(?string $montantPaye): self
     {
          $this->montantPaye = $montantPaye;
          return $this;
     }

     public function getStatusPaiement(): ?string
     {
          return $this->statusPaiement;
     }

     public function setStatusPaiement(?string $statusPaiement): self
     {
          $this->statusPaiement = $statusPaiement;
          return $this;
     }

     public function getDatePaiement(): ?\DateTimeInterface
     {
          return $this->datePaiement;
     }

     public function setDatePaiement(?\DateTimeInterface $datePaiement): self
     {
          $this->datePaiement = $datePaiement;
          return $this;
     }
}
