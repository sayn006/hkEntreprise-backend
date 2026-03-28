<?php

namespace App\Entity;

use App\Repository\SuiviFactureRepository;
use App\Repository\UploadsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: SuiviFactureRepository::class)]
class SuiviFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\Column(length: 255)]
    private ?string $factureNumero = null;

    #[ORM\Column(nullable: true)]
    private ?float $montantHt = null;

    #[ORM\ManyToOne]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'suiviFactures')]
    private ?Chantier $chantier = null;

    #[ORM\Column(nullable: true)]
    private ?float $manqueReglementTtc = null;

    #[ORM\Column(nullable: true)]
    private ?float $penalitesRevisionPrix = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalApayerTtc = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReglement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;

    #[ORM\Column(nullable: true)]
    private ?int $prorata = null;

    #[ORM\Column(nullable: true)]
    private ?int $prorataPercent = null;

    #[ORM\Column(nullable: true)]
    private ?int $rgHt = null;

    #[ORM\Column(nullable: true)]
    private ?int $tva = null;

    // Ajoutez un setter pour l'UploadRepository via une méthode statique ou via un constructeur
    private UploadsRepository $uploadsRepository;

    // Injecter le repository via un setter ou un constructeur
    public function setUploadRepository(UploadsRepository $uploadsRepository): void
    {
        $this->uploadsRepository = $uploadsRepository;
    }

    // Méthode pour récupérer les uploads associés à cette entité
    public function getUploads(): array
    {
        // Vous pouvez maintenant accéder au repository à l'intérieur de l'entité
        return $this->uploadsRepository->findDocumentsByEntityTypeAndId('SuiviFacture', $this->id);
    }

    /**
     * Cette méthode ajoute un fichier à la collection des uploads de la SuiviFacture.
     */
    public function addUploads(UploadedFile $file, EntityManagerInterface $entityManager): void
    {
        $upload = new Uploads();

        // Création du nom unique pour le fichier
        $uniqueFilename = bin2hex(random_bytes(6)) . '.' . $file->guessExtension();
        $file->move('uploads', $uniqueFilename);

        // Assignation des informations du fichier à l'objet Upload
        $upload->setUniqueFileName($uniqueFilename);
        $upload->setFileName($file->getClientOriginalName());
        $upload->setEntityType('SuiviFacture');
        $upload->setEntityId($this->getId());

        // Enregistrement de l'objet Upload
        $entityManager->persist($upload);
        $entityManager->flush();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(?\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getFactureNumero(): ?string
    {
        return $this->factureNumero;
    }

    public function setFactureNumero(string $factureNumero): static
    {
        $this->factureNumero = $factureNumero;

        return $this;
    }

    public function getMontantHt(): ?float
    {
        return $this->montantHt;
    }

    public function setMontantHt(?float $montantHt): static
    {
        $this->montantHt = $montantHt;

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


    public function getManqueReglementTtc(): ?float
    {
        return $this->manqueReglementTtc;
    }

    public function setManqueReglementTtc(?float $manqueReglementTtc): static
    {
        $this->manqueReglementTtc = $manqueReglementTtc;

        return $this;
    }

    public function getPenalitesRevisionPrix(): ?float
    {
        return $this->penalitesRevisionPrix;
    }

    public function setPenalitesRevisionPrix(?float $penalitesRevisionPrix): static
    {
        $this->penalitesRevisionPrix = $penalitesRevisionPrix;

        return $this;
    }

    public function getProrataHt(): ?float
    {
        return $this->prorataHt;
    }


    public function getTotalApayerTtc(): ?float
    {
        return $this->totalApayerTtc;
    }

    public function setTotalApayerTtc(?float $totalApayerTtc): static
    {
        $this->totalApayerTtc = $totalApayerTtc;

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

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;

        return $this;
    }

    public function getProrata(): ?int
    {
        return $this->prorata;
    }

    public function setProrata(?int $prorata): static
    {
        $this->prorata = $prorata;

        return $this;
    }

    public function getProrataPercent(): ?int
    {
        return $this->prorataPercent;
    }

    public function setProrataPercent(?int $prorataPercent): static
    {
        $this->prorataPercent = $prorataPercent;

        return $this;
    }

    public function getRgHt(): ?int
    {
        return $this->rgHt;
    }

    public function setRgHt(?int $rgHt): static
    {
        $this->rgHt = $rgHt;

        return $this;
    }


    public function getTva(): ?int
    {
        return $this->tva;
    }

    public function setTva(?int $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

}
