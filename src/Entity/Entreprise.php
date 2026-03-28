<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
#[ApiResource(
    operations: [new GetCollection(), new Get(), new Post(), new Patch(), new Delete()],
    normalizationContext: ['groups' => ['entreprise:read']],
    denormalizationContext: ['groups' => ['entreprise:write']],
)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['entreprise:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $formeJuridique = null;

    #[ORM\Column(length: 14, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $ville = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $siteWeb = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $logo = null;

    // Mentions légales
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $rcs = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $villeRcs = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $capital = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $tvaIntracommunautaire = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $codeNaf = null;

    // Coordonnées bancaires
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $banque = null;

    #[ORM\Column(length: 40, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $iban = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $bic = null;

    // PDF - conditions par défaut
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $validiteOffre = '30 jours';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $delaiExecution = 'À convenir';

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $modeReglement = 'Selon conditions générales';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['entreprise:read', 'entreprise:write'])]
    private ?string $mentionsLegales = null;

    // Getters / Setters
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }
    public function getFormeJuridique(): ?string { return $this->formeJuridique; }
    public function setFormeJuridique(?string $v): static { $this->formeJuridique = $v; return $this; }
    public function getSiret(): ?string { return $this->siret; }
    public function setSiret(?string $siret): static { $this->siret = $siret; return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): static { $this->adresse = $adresse; return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(?string $v): static { $this->codePostal = $v; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(?string $ville): static { $this->ville = $ville; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getSiteWeb(): ?string { return $this->siteWeb; }
    public function setSiteWeb(?string $v): static { $this->siteWeb = $v; return $this; }
    public function getLogo(): ?string { return $this->logo; }
    public function setLogo(?string $logo): static { $this->logo = $logo; return $this; }
    public function getRcs(): ?string { return $this->rcs; }
    public function setRcs(?string $v): static { $this->rcs = $v; return $this; }
    public function getVilleRcs(): ?string { return $this->villeRcs; }
    public function setVilleRcs(?string $v): static { $this->villeRcs = $v; return $this; }
    public function getCapital(): ?string { return $this->capital; }
    public function setCapital(?string $v): static { $this->capital = $v; return $this; }
    public function getTvaIntracommunautaire(): ?string { return $this->tvaIntracommunautaire; }
    public function setTvaIntracommunautaire(?string $v): static { $this->tvaIntracommunautaire = $v; return $this; }
    public function getCodeNaf(): ?string { return $this->codeNaf; }
    public function setCodeNaf(?string $v): static { $this->codeNaf = $v; return $this; }
    public function getBanque(): ?string { return $this->banque; }
    public function setBanque(?string $v): static { $this->banque = $v; return $this; }
    public function getIban(): ?string { return $this->iban; }
    public function setIban(?string $v): static { $this->iban = $v; return $this; }
    public function getBic(): ?string { return $this->bic; }
    public function setBic(?string $v): static { $this->bic = $v; return $this; }
    public function getValiditeOffre(): ?string { return $this->validiteOffre; }
    public function setValiditeOffre(?string $v): static { $this->validiteOffre = $v; return $this; }
    public function getDelaiExecution(): ?string { return $this->delaiExecution; }
    public function setDelaiExecution(?string $v): static { $this->delaiExecution = $v; return $this; }
    public function getModeReglement(): ?string { return $this->modeReglement; }
    public function setModeReglement(?string $v): static { $this->modeReglement = $v; return $this; }
    public function getMentionsLegales(): ?string { return $this->mentionsLegales; }
    public function setMentionsLegales(?string $v): static { $this->mentionsLegales = $v; return $this; }
}
