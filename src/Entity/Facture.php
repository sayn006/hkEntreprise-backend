<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use App\State\FactureProcessor;
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

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(processor: FactureProcessor::class),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['facture:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['facture:write']],
)]
#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['facture:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?string $numero = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['facture:read', 'facture:write'])]
    #[MaxDepth(1)]
    private ?Client $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['facture:read', 'facture:write'])]
    #[MaxDepth(1)]
    private ?Chantier $chantier = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?\DateTimeInterface $dateReglement = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private ?string $statut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;

    /**
     * @var Collection<int, FactureLigne>
     */
    #[ORM\OneToMany(targetEntity: FactureLigne::class, mappedBy: 'facture', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['facture:read', 'facture:write'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function getChantier(): ?Chantier
    {
        return $this->chantier;
    }

    public function setChantier(?Chantier $chantier): static
    {
        $this->chantier = $chantier;
        return $this;
    }

    public function getDateReglement(): ?\DateTimeInterface
    {
        return $this->dateReglement;
    }

    public function setDateReglement(?\DateTimeInterface $dateReglement): static
    {
        $this->dateReglement = $dateReglement;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;
        return $this;
    }

    /**
     * @return Collection<int, FactureLigne>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(FactureLigne $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setFacture($this);
        }
        return $this;
    }

    public function removeLigne(FactureLigne $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getFacture() === $this) {
                $ligne->setFacture(null);
            }
        }
        return $this;
    }

    public function getMontantTotalHt(): string
    {
        $total = 0;
        foreach ($this->situations as $situation) {
            $total += floatval($situation->getMontantHt());
        }
        return number_format($total, 2, '.', '');
    }

    public function getMontantTotalTtc(): string
    {
        $total = 0;
        foreach ($this->situations as $situation) {
            $total += floatval($situation->getMontantTtc());
        }
        return number_format($total, 2, '.', '');
    }

    public function getMontantTotalTravaux(): string
    {
        $total = 0;
        foreach ($this->situations as $situation) {
            $total += floatval($situation->getMontantTravaux());
        }
        return number_format($total, 2, '.', '');
    }

    public function getMontantTotalRetenueGarantie(): string
    {
        $total = 0;
        foreach ($this->situations as $situation) {
            $total += floatval($situation->getRetenueGarantie());
        }
        return number_format($total, 2, '.', '');
    }

    public function getMontantTotalTva(): string
    {
        $total = 0;
        foreach ($this->situations as $situation) {
            $total += floatval($situation->getMontantTva());
        }
        return number_format($total, 2, '.', '');
    }

    public function getMontantTotalProrata(): string
    {
        $total = 0;
        foreach ($this->situations as $situation) {
            if ($situation->getMontantProrata()) {
                $total += floatval($situation->getMontantProrata());
            }
        }
        return number_format($total, 2, '.', '');
    }
} 