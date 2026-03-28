<?php

namespace App\Entity;

use App\Repository\BonCommandeArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BonCommandeArticleRepository::class)]
class BonCommandeArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bonCommandeArticles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BonCommande $bonCommande = null;

    #[ORM\Column(length: 150)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $designation = null;

    #[ORM\Column(length: 10)]
    private ?int $qte = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixUnitaireHt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBonCommande(): ?BonCommande
    {
        return $this->bonCommande;
    }

    public function setBonCommande(?BonCommande $bonCommande): static
    {
        $this->bonCommande = $bonCommande;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }


    public function getQte(): ?string
    {
        return $this->qte;
    }

    public function setQte(string $qte): static
    {
        $this->qte = $qte;

        return $this;
    }

    public function getPrixUnitaireHt(): ?float
    {
        return $this->prixUnitaireHt;
    }

    public function setPrixUnitaireHt(?float $prixUnitaireHt): static
    {
        $this->prixUnitaireHt = $prixUnitaireHt;

        return $this;
    }

}
