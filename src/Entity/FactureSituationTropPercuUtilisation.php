<?php

namespace App\Entity;

use App\Repository\FactureSituationTropPercuUtilisationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureSituationTropPercuUtilisationRepository::class)]
#[ORM\Table(name: 'facture_situation_trop_percu_utilisation')]
class FactureSituationTropPercuUtilisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FactureSituationTropPercu::class, inversedBy: 'utilisations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FactureSituationTropPercu $tropPercu = null;

    #[ORM\ManyToOne(targetEntity: FactureSituation::class, inversedBy: 'tropPercusAppliques')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FactureSituation $factureSituation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateUtilisation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisePar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->dateUtilisation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTropPercu(): ?FactureSituationTropPercu
    {
        return $this->tropPercu;
    }

    public function setTropPercu(?FactureSituationTropPercu $tropPercu): static
    {
        $this->tropPercu = $tropPercu;
        return $this;
    }

    public function getFactureSituation(): ?FactureSituation
    {
        return $this->factureSituation;
    }

    public function setFactureSituation(?FactureSituation $factureSituation): static
    {
        $this->factureSituation = $factureSituation;
        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getDateUtilisation(): ?\DateTimeInterface
    {
        return $this->dateUtilisation;
    }

    public function setDateUtilisation(\DateTimeInterface $dateUtilisation): static
    {
        $this->dateUtilisation = $dateUtilisation;
        return $this;
    }

    public function getUtilisePar(): ?User
    {
        return $this->utilisePar;
    }

    public function setUtilisePar(?User $utilisePar): static
    {
        $this->utilisePar = $utilisePar;
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
}
