<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\SituationRepository;
use App\State\SituationGenerationProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(processor: SituationGenerationProcessor::class),
        new Patch(),
        new Delete(),
    ]
)]
#[ORM\Entity(repositoryClass: SituationRepository::class)]
class Situation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebutPeriode = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFinPeriode = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $montantTravaux = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $retenueGarantie = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $montantTva = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $prorata = false;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $prorataPercent = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $montantProrata = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $montantHt = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $montantTtc = null;

    #[ORM\ManyToOne(inversedBy: 'situations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facture $facture = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $retenueGarantiePercent = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $tvaPercent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getDateDebutPeriode(): ?\DateTimeInterface
    {
        return $this->dateDebutPeriode;
    }

    public function setDateDebutPeriode(\DateTimeInterface $dateDebutPeriode): static
    {
        $this->dateDebutPeriode = $dateDebutPeriode;
        return $this;
    }

    public function getDateFinPeriode(): ?\DateTimeInterface
    {
        return $this->dateFinPeriode;
    }

    public function setDateFinPeriode(\DateTimeInterface $dateFinPeriode): static
    {
        $this->dateFinPeriode = $dateFinPeriode;
        return $this;
    }

    public function getMontantTravaux(): ?float
    {
        return $this->montantTravaux;
    }

    public function setMontantTravaux(float $montantTravaux): static
    {
        $this->montantTravaux = $montantTravaux;
        return $this;
    }

    public function getRetenueGarantie(): ?float
    {
        return $this->retenueGarantie;
    }

    public function setRetenueGarantie(float $retenueGarantie): static
    {
        $this->retenueGarantie = $retenueGarantie;
        return $this;
    }

    public function getMontantTva(): ?float
    {
        return $this->montantTva;
    }

    public function setMontantTva(float $montantTva): static
    {
        $this->montantTva = $montantTva;
        return $this;
    }

    public function isProrata(): bool
    {
        return $this->prorata;
    }

    public function setProrata(bool $prorata): static
    {
        $this->prorata = $prorata;
        return $this;
    }

    public function getProrataPercent(): ?float
    {
        return $this->prorataPercent;
    }

    public function setProrataPercent(?float $prorataPercent): static
    {
        $this->prorataPercent = $prorataPercent;
        return $this;
    }

    public function getMontantProrata(): ?float
    {
        return $this->montantProrata;
    }

    public function setMontantProrata(?float $montantProrata): static
    {
        $this->montantProrata = $montantProrata;
        return $this;
    }

    public function getMontantHt(): ?float
    {
        return $this->montantHt;
    }

    public function setMontantHt(float $montantHt): static
    {
        $this->montantHt = $montantHt;
        return $this;
    }

    public function getMontantTtc(): ?float
    {
        return $this->montantTtc;
    }

    public function setMontantTtc(float $montantTtc): static
    {
        $this->montantTtc = $montantTtc;
        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;
        return $this;
    }

    public function getRetenueGarantiePercent(): ?float
    {
        return $this->retenueGarantiePercent;
    }

    public function setRetenueGarantiePercent(?float $retenueGarantiePercent): static
    {
        $this->retenueGarantiePercent = $retenueGarantiePercent;
        return $this;
    }

    public function getTvaPercent(): ?float
    {
        return $this->tvaPercent;
    }

    public function setTvaPercent(?float $tvaPercent): static
    {
        $this->tvaPercent = $tvaPercent;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function calculateMontants(): void
    {
        // Validation et conversion du montant des travaux
        if (!is_numeric($this->montantTravaux)) {
            throw new \InvalidArgumentException('Le montant des travaux doit être un nombre valide');
        }
        $montantTravaux = round(floatval($this->montantTravaux), 2);

        // Validation des pourcentages
        if (!is_numeric($this->retenueGarantiePercent)) {
            throw new \InvalidArgumentException('Le pourcentage de retenue de garantie doit être un nombre valide');
        }
        if (!is_numeric($this->tvaPercent)) {
            throw new \InvalidArgumentException('Le pourcentage de TVA doit être un nombre valide');
        }
        if (!is_numeric($this->prorataPercent)) {
            throw new \InvalidArgumentException('Le pourcentage de prorata doit être un nombre valide');
        }

        // Calcul de la retenue de garantie
        $retenueGarantie = $montantTravaux * floatval($this->retenueGarantiePercent);
        $this->retenueGarantie = round($retenueGarantie, 2);

        // Calcul du prorata directement à partir du pourcentage
        $montantProrata = $montantTravaux * floatval($this->prorataPercent);
        $this->montantProrata = round($montantProrata, 2);

        // Calcul du montant HT
        $montantHt = $montantTravaux - $this->retenueGarantie - $this->montantProrata;
        $this->montantHt = round($montantHt, 2);

        // Calcul de la TVA
        $montantTva = $this->montantHt * floatval($this->tvaPercent);
        $this->montantTva = round($montantTva, 2);

        // Calcul du montant TTC
        $montantTtc = $this->montantHt + $this->montantTva;
        $this->montantTtc = round($montantTtc, 2);
    }
}
