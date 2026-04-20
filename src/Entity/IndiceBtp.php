<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\IndiceBtpRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Indice BTP utilisé pour la révision de prix sur les marchés révisables.
 * Exemples : BT01 (bâtiment tous corps d'état), TP01 (travaux publics).
 *
 * Un indice par (type, mois) : mois = premier jour du mois.
 */
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['indice_btp:read']],
    denormalizationContext: ['groups' => ['indice_btp:write']],
    paginationEnabled: false,
)]
#[ApiFilter(SearchFilter::class, properties: ['type' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['mois', 'type'])]
#[ORM\Entity(repositoryClass: IndiceBtpRepository::class)]
#[ORM\Table(name: 'indice_btp')]
#[ORM\UniqueConstraint(name: 'uq_indice_type_mois', columns: ['type', 'mois'])]
class IndiceBtp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['indice_btp:read'])]
    private ?int $id = null;

    /**
     * Code de l'indice : BT01, TP01, BT40, BT50, etc.
     */
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Groups(['indice_btp:read', 'indice_btp:write', 'chantier:read'])]
    private ?string $type = null;

    /**
     * Premier jour du mois de référence (ex : 2026-04-01).
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull]
    #[Groups(['indice_btp:read', 'indice_btp:write'])]
    private ?\DateTimeInterface $mois = null;

    /**
     * Valeur de l'indice pour ce type et ce mois (ex : 127.5).
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    #[Assert\NotNull]
    #[Assert\Positive]
    #[Groups(['indice_btp:read', 'indice_btp:write'])]
    private ?string $valeur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['indice_btp:read'])]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): self { $this->type = strtoupper(trim($type)); return $this; }

    public function getMois(): ?\DateTimeInterface { return $this->mois; }
    public function setMois(\DateTimeInterface $mois): self
    {
        // Normalise au 1er du mois
        $first = (clone $mois)->modify('first day of this month');
        $first->setTime(0, 0, 0);
        $this->mois = $first;
        return $this;
    }

    public function getValeur(): ?string { return $this->valeur; }
    public function setValeur(string|float $valeur): self { $this->valeur = (string) $valeur; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }
}
