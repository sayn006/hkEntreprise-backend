<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\DevisDetail;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Import de lignes de devis depuis un fichier Excel (xlsx).
 *
 * Workflow :
 *  - GET  /api/devis-import/template         → xlsx modèle à télécharger
 *  - POST /api/devis/{id}/import-preview     → upload xlsx, renvoie colonnes + suggestion mapping
 *  - POST /api/devis/{id}/import             → applique mapping, crée les DevisDetail
 *
 * Note: la route template est hors de /api/devis/* pour éviter le conflit
 * avec la route ApiPlatform `/api/devis/{id}` (Get item).
 */
#[IsGranted('ROLE_USER')]
class DevisImportController extends AbstractController
{
    private const MAX_UPLOAD_SIZE = 2 * 1024 * 1024; // 2 Mo

    /** Champs cibles exposés au mapping (clé = nom côté front). */
    private const TARGET_FIELDS = [
        'reference'    => 'Référence',
        'designation'  => 'Désignation',
        'quantite'     => 'Quantité',
        'unite'        => 'Unité',
        'prixUnitaire' => 'Prix unitaire HT',
        'tva'          => 'TVA %',
        'type'         => 'Type',
    ];

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Génère et renvoie un fichier XLSX modèle avec en-têtes français et exemples.
     */
    #[Route('/api/devis-import/template', name: 'api_devis_import_template', methods: ['GET'])]
    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lignes de devis');

        $headers = ['Référence', 'Désignation', 'Quantité', 'Unité', 'Prix unitaire HT', 'TVA %', 'Type'];
        $sheet->fromArray($headers, null, 'A1');

        // Exemples
        $sheet->fromArray([
            ['REF-001', 'Fourniture et pose de dalle béton', 25, 'm²', 120.50, 20, 'DETAIL'],
            ['REF-002', 'Main d\'œuvre maçonnerie',           10, 'H',  55.00,  20, 'DETAIL'],
            ['',         'Bloc Lot 1 — Gros œuvre',             null, '', null,  null, 'GROUPE'],
        ], null, 'A2');

        // Style header
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'modele-import-devis.xlsx';
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new XlsxWriter($spreadsheet);
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Parse le xlsx uploadé et renvoie colonnes détectées + suggestion de mapping.
     */
    #[Route('/api/devis/{id}/import-preview', name: 'api_devis_import_preview', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function preview(Devis $devis, Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'Fichier manquant (champ "file" attendu).'], 400);
        }
        if ($file->getSize() > self::MAX_UPLOAD_SIZE) {
            return $this->json(['error' => 'Fichier trop volumineux (max 2 Mo).'], 413);
        }
        try {
            $rows = $this->readXlsx($file->getPathname());
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Lecture XLSX impossible : ' . $e->getMessage()], 400);
        }

        if (count($rows) < 1) {
            return $this->json(['error' => 'Fichier vide.'], 400);
        }

        $headers = array_map(fn($v) => is_string($v) ? trim($v) : (string)$v, $rows[0]);
        $dataRows = array_slice($rows, 1);
        // Filtrer les lignes entièrement vides
        $dataRows = array_values(array_filter($dataRows, function ($r) {
            foreach ($r as $v) {
                if ($v !== null && $v !== '' && $v !== false) return true;
            }
            return false;
        }));

        $samples = array_slice($dataRows, 0, 5);

        $suggestedMapping = [];
        foreach ($headers as $h) {
            if ($h === '') continue;
            $suggestedMapping[$h] = $this->suggestField($h);
        }

        return $this->json([
            'detectedColumns'   => array_values(array_filter($headers, fn($h) => $h !== '')),
            'samples'           => $samples,
            'suggestedMapping'  => $suggestedMapping,
            'totalRows'         => count($dataRows),
            'targetFields'      => self::TARGET_FIELDS,
        ]);
    }

    /**
     * Importe les lignes dans le devis selon le mapping fourni.
     *
     * multipart: file=xlsx, mapping=JSON string (ex: {"Désignation":"designation","Qté":"quantite"})
     */
    #[Route('/api/devis/{id}/import', name: 'api_devis_import', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function import(Devis $devis, Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'Fichier manquant (champ "file" attendu).'], 400);
        }
        if ($file->getSize() > self::MAX_UPLOAD_SIZE) {
            return $this->json(['error' => 'Fichier trop volumineux (max 2 Mo).'], 413);
        }

        $mappingRaw = $request->request->get('mapping', '{}');
        $mapping = json_decode((string)$mappingRaw, true);
        if (!is_array($mapping)) {
            return $this->json(['error' => 'Mapping invalide.'], 400);
        }

        try {
            $rows = $this->readXlsx($file->getPathname());
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Lecture XLSX impossible : ' . $e->getMessage()], 400);
        }
        if (count($rows) < 2) {
            return $this->json(['error' => 'Aucune ligne à importer.'], 400);
        }

        $headers = array_map(fn($v) => is_string($v) ? trim($v) : (string)$v, $rows[0]);
        $dataRows = array_slice($rows, 1);

        // Trouver le displayOrder max actuel
        $maxOrder = 0;
        foreach ($devis->getDevisDetails() as $existing) {
            if ($existing->getDisplayOrder() > $maxOrder) {
                $maxOrder = $existing->getDisplayOrder();
            }
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($dataRows as $i => $row) {
            // Skip ligne entièrement vide
            $nonEmpty = false;
            foreach ($row as $v) {
                if ($v !== null && $v !== '' && $v !== false) { $nonEmpty = true; break; }
            }
            if (!$nonEmpty) { $skipped++; continue; }

            $values = [];
            foreach ($headers as $idx => $h) {
                if (!isset($mapping[$h]) || $mapping[$h] === '' || $mapping[$h] === null) continue;
                $field = $mapping[$h];
                $values[$field] = $row[$idx] ?? null;
            }

            // designation requise
            $designation = isset($values['designation']) ? trim((string)$values['designation']) : '';
            if ($designation === '') {
                $skipped++;
                $errors[] = sprintf('Ligne %d : designation manquante, ignorée.', $i + 2);
                continue;
            }

            try {
                $detail = new DevisDetail();
                $detail->setDevis($devis);
                $detail->setDesignation($designation);

                if (isset($values['reference']) && $values['reference'] !== '') {
                    $detail->setReference((string)$values['reference']);
                }
                if (isset($values['quantite']) && $values['quantite'] !== '') {
                    $detail->setQuantite((int)$values['quantite']);
                }
                if (isset($values['unite']) && $values['unite'] !== '') {
                    $detail->setUnite((string)$values['unite']);
                }
                if (isset($values['prixUnitaire']) && $values['prixUnitaire'] !== '') {
                    $detail->setPrixUnitaire((float)str_replace(',', '.', (string)$values['prixUnitaire']));
                }
                if (isset($values['tva']) && $values['tva'] !== '') {
                    $detail->setTva((float)str_replace(',', '.', (string)$values['tva']));
                } else {
                    $detail->setTva(20.0);
                }

                $type = isset($values['type']) && $values['type'] !== '' ? strtoupper((string)$values['type']) : 'DETAIL';
                $detail->setType($type);

                // lineType : BH si GROUPE, C si COMMENTAIRE, RL sinon
                if ($type === 'GROUPE') {
                    $detail->setLineType('BH');
                    $detail->setIsBlockHeader(true);
                } elseif ($type === 'COMMENTAIRE') {
                    $detail->setLineType('C');
                } else {
                    $detail->setLineType('RL');
                }

                $maxOrder++;
                $detail->setDisplayOrder($maxOrder);

                $this->em->persist($detail);
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = sprintf('Ligne %d : %s', $i + 2, $e->getMessage());
            }
        }

        if ($created > 0) {
            $this->em->flush();
        }

        return $this->json([
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    /**
     * Lit toutes les cellules du premier sheet et renvoie un tableau de rows.
     *
     * @return array<int, array<int, mixed>>
     */
    private function readXlsx(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        return $sheet->toArray(null, true, true, false);
    }

    /**
     * Devine le champ cible à partir d'un nom de colonne (similarité basique par mots-clés).
     */
    private function suggestField(string $colName): string
    {
        $s = $this->normalize($colName);
        // Ordre : du plus spécifique au plus générique. Les clés dont un
        // mot-clé est sous-chaîne d'un autre (ex: "unit" ⊂ "unitaire")
        // doivent passer après le plus long.
        $map = [
            'prixUnitaire' => ['prixunitaireht', 'prixunitaire', 'puht', 'pu', 'prix', 'price'],
            'tva'          => ['tva', 'vat', 'tax'],
            'quantite'     => ['quantite', 'qte', 'qty', 'quantity'],
            'designation'  => ['designation', 'description', 'libelle', 'intitule', 'nom'],
            'reference'    => ['reference', 'ref', 'code'],
            'unite'        => ['unite', 'unit'],
            'type'         => ['type', 'categorie', 'kind'],
        ];
        foreach ($map as $field => $keywords) {
            foreach ($keywords as $kw) {
                if ($s === $kw || str_contains($s, $kw)) {
                    return $field;
                }
            }
        }
        return '';
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $translit = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($translit !== false) $s = $translit;
        return preg_replace('/[^a-z0-9]/', '', $s) ?? '';
    }
}
