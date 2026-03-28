<?php

namespace App\DataFixtures;

use App\Entity\Chantier;
use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\DevisDetail;
use App\Entity\Entreprise;
use App\Entity\FormeJuridique;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // --- Entreprise ---
        $entreprise = new Entreprise();
        $entreprise->setNom('HK Construction');
        $entreprise->setFormeJuridique('SARL');
        $entreprise->setSiret('81861590800012');
        $entreprise->setAdresse('45 avenue du Général de Gaulle');
        $entreprise->setCodePostal('92160');
        $entreprise->setVille('Antony');
        $entreprise->setTelephone('01 46 74 00 00');
        $entreprise->setEmail('contact@hk-construction.fr');
        $entreprise->setSiteWeb('https://hk-construction.fr');
        $entreprise->setCapital('50 000 €');
        $entreprise->setRcs('818615908');
        $entreprise->setVilleRcs('NANTERRE');
        $entreprise->setTvaIntracommunautaire('FR 12 818615908');
        $entreprise->setCodeNaf('4391A');
        $entreprise->setBanque('Société Générale');
        $entreprise->setIban('FR76 3000 3032 1700 0200 0000 000');
        $entreprise->setBic('SOGEFRPP');
        $entreprise->setValiditeOffre('30 jours');
        $entreprise->setDelaiExecution('Selon planning contractuel');
        $entreprise->setModeReglement('30 jours fin de mois');
        $manager->persist($entreprise);

        // --- Formes juridiques ---
        $sarl = new FormeJuridique();
        $sarl->setNom('SARL');
        $manager->persist($sarl);

        $sas = new FormeJuridique();
        $sas->setNom('SAS');
        $manager->persist($sas);

        $sa = new FormeJuridique();
        $sa->setNom('SA');
        $manager->persist($sa);

        $manager->flush();

        // --- User admin ---
        $admin = new User();
        $admin->setUsername('admin@hk.fr');
        $admin->setNom('Martin');
        $admin->setPrenom('Jean');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // --- Clients ---
        $clientsData = [
            ['Groupe Vinci Construction', $sas, 'Paris', '75008', '01 47 16 35 00', 'contact@vinci-construction.fr'],
            ['Bouygues Immobilier Île-de-France', $sa, 'Saint-Quentin-en-Yvelines', '78280', '01 30 60 00 00', 'contact@bouygues-immo.fr'],
            ['Eiffage Construction Grand Paris', $sas, 'Vélizy-Villacoublay', '78140', '01 34 65 00 00', 'contact@eiffage.fr'],
            ['Société Générale Immobilier', $sa, 'La Défense', '92800', '01 42 14 00 00', 'immobilier@socgen.fr'],
        ];

        $clients = [];
        foreach ($clientsData as [$nom, $fj, $ville, $cp, $tel, $email]) {
            $client = new Client();
            $client->setRaisonSocial($nom);
            $client->setFormeJuridique($fj);
            $client->setVille($ville);
            $client->setCodePostal($cp);
            $client->setTelephone($tel);
            $client->setEmail($email);
            $manager->persist($client);
            $clients[] = $client;
        }

        $manager->flush();

        // --- Chantiers ---
        $chantiersData = [
            ['CH-2026-001', 'Réhabilitation résidence Les Pins — Bâtiment A', 'Antony', '45 rue des Pins'],
            ['CH-2026-002', 'Construction bureaux Zone Technologique Nord', 'Massy', '12 allée des Innovations'],
            ['CH-2026-003', 'Rénovation façades immeuble Haussmann', 'Paris 8ème', '38 boulevard Haussmann'],
        ];

        $chantiers = [];
        foreach ($chantiersData as [$code, $nom, $ville, $adresse]) {
            $chantier = new Chantier();
            $chantier->setCode($code);
            $chantier->setNom($nom);
            $chantier->setSlug(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $code)));
            $chantier->setSoftDelete(false);
            $manager->persist($chantier);
            $chantiers[] = $chantier;
        }

        $manager->flush();

        // --- Devis 1 : Brouillon (pas de lignes) ---
        $devis1 = new Devis();
        $devis1->setNumero('DEVIS202603001');
        $devis1->setTitre('Réhabilitation toiture-terrasse — Lot 1 Étanchéité');
        $devis1->setEtat('EtatDevisBrouillon');
        $devis1->setDateCreation(new \DateTime('-15 days'));
        $devis1->setCoefficientMateriel(1.0);
        $devis1->setCoefficientMainOeuvre(1.0);
        $devis1->setTauxMainOeuvre(0.0);
        $manager->persist($devis1);

        // --- Devis 2 : Envoyé avec lignes ---
        $devis2 = new Devis();
        $devis2->setNumero('DEVIS202603002');
        $devis2->setTitre('Ravalement façades + isolation thermique par l\'extérieur');
        $devis2->setEtat('EtatDevisEnvoye');
        $devis2->setDateCreation(new \DateTime('-30 days'));
        $devis2->setCoefficientMateriel(1.0);
        $devis2->setCoefficientMainOeuvre(1.0);
        $devis2->setTauxMainOeuvre(0.0);
        $manager->persist($devis2);

        // Lignes devis 2 — Bloc 1 : Préparation
        $this->addBlocHeader($manager, $devis2, 'BLOC 1 — Préparation et installation de chantier', '1', 0);
        $this->addLigne($manager, $devis2, 'PRE-001', 'Installation de chantier et base vie', 1, 4500.00, 'ens', '20', '1', 1);
        $this->addLigne($manager, $devis2, 'PRE-002', 'Échafaudage tube et coupon — façades N/S/E/O', 680, 12.50, 'm²', '20', '1', 2);
        $this->addLigne($manager, $devis2, 'PRE-003', 'Protection sols, baies et parties communes', 1, 2800.00, 'ens', '20', '1', 3);

        // Bloc 2 : Ravalement
        $this->addBlocHeader($manager, $devis2, 'BLOC 2 — Ravalement et préparation support', '2', 4);
        $this->addLigne($manager, $devis2, 'RAV-001', 'Piquage et dépose ancien enduit dégradé', 680, 18.00, 'm²', '10', '2', 5);
        $this->addLigne($manager, $devis2, 'RAV-002', 'Traitement fissures et reprises maçonnerie', 40, 85.00, 'ml', '10', '2', 6);
        $this->addLigne($manager, $devis2, 'RAV-003', 'Enduit de rebouchage et égalisation', 680, 22.00, 'm²', '10', '2', 7);
        $this->addLigne($manager, $devis2, 'RAV-004', 'Primaire d\'accrochage et sous-couche', 680, 8.50, 'm²', '10', '2', 8);
        $this->addLigne($manager, $devis2, 'RAV-005', 'Enduit de finition gratté fin — teinte RAL 9001', 680, 32.00, 'm²', '10', '2', 9);

        // Bloc 3 : ITE
        $this->addBlocHeader($manager, $devis2, 'BLOC 3 — Isolation thermique par l\'extérieur (ITE)', '3', 10);
        $this->addLigne($manager, $devis2, 'ITE-001', 'Panneaux PSE blanc 14cm — collage + fixation mécanique', 520, 65.00, 'm²', '10', '3', 11);
        $this->addLigne($manager, $devis2, 'ITE-002', 'Treillis fibre de verre et sous-enduit', 520, 18.00, 'm²', '10', '3', 12);
        $this->addLigne($manager, $devis2, 'ITE-003', 'Enduit de finition silicoxylé — teinte coordonnée', 520, 28.00, 'm²', '10', '3', 13);
        $this->addLigne($manager, $devis2, 'ITE-004', 'Profils d\'appui, départ et encadrement', 380, 12.00, 'ml', '10', '3', 14);

        $manager->flush();

        // --- Devis 3 : Accepté avec chantier lié et lignes complètes ---
        $devis3 = new Devis();
        $devis3->setNumero('DEVIS202603003');
        $devis3->setTitre('Construction parking souterrain R-2 — Gros Œuvre & VRD');
        $devis3->setEtat('EtatDevisAccepte');
        $devis3->setDateCreation(new \DateTime('-60 days'));
        $devis3->setChantier($chantiers[1]);
        $devis3->setCoefficientMateriel(1.0);
        $devis3->setCoefficientMainOeuvre(1.0);
        $devis3->setTauxMainOeuvre(0.0);
        $manager->persist($devis3);

        // Bloc 1 : Terrassement
        $this->addBlocHeader($manager, $devis3, 'BLOC 1 — Terrassement et fouilles', '1', 0);
        $this->addLigne($manager, $devis3, 'TER-001', 'Terrassement général en grande masse', 4500, 28.00, 'm³', '20', '1', 1);
        $this->addLigne($manager, $devis3, 'TER-002', 'Fouilles en rigole pour fondations', 320, 65.00, 'm³', '20', '1', 2);
        $this->addLigne($manager, $devis3, 'TER-003', 'Évacuation déblais en décharge agréée', 4820, 18.50, 'm³', '20', '1', 3);
        $this->addLigne($manager, $devis3, 'TER-004', 'Remblai sélectionné et compactage', 800, 22.00, 'm³', '20', '1', 4);

        // Bloc 2 : Béton armé
        $this->addBlocHeader($manager, $devis3, 'BLOC 2 — Béton armé — Radier et voiles', '2', 5);
        $this->addLigne($manager, $devis3, 'BA-001', 'Béton de propreté dosé 150 kg/m³', 180, 95.00, 'm³', '20', '2', 6);
        $this->addLigne($manager, $devis3, 'BA-002', 'Radier en béton armé ép.30cm — C25/30', 620, 185.00, 'm³', '20', '2', 7);
        $this->addLigne($manager, $devis3, 'BA-003', 'Voiles béton armé ép.20cm — C30/37', 940, 220.00, 'm³', '20', '2', 8);
        $this->addLigne($manager, $devis3, 'BA-004', 'Poutres et nervures', 85, 380.00, 'm³', '20', '2', 9);
        $this->addLigne($manager, $devis3, 'BA-005', 'Dalles plein pied R-1 et R-2 — ép.25cm', 420, 195.00, 'm³', '20', '2', 10);

        // Bloc 3 : Étanchéité
        $this->addBlocHeader($manager, $devis3, 'BLOC 3 — Étanchéité et drainage', '3', 11);
        $this->addLigne($manager, $devis3, 'ETA-001', 'Membrane d\'étanchéité bitumineuse double couche', 1240, 38.00, 'm²', '10', '3', 12);
        $this->addLigne($manager, $devis3, 'ETA-002', 'Drainage géotextile + couche drainante', 1240, 22.00, 'm²', '10', '3', 13);
        $this->addLigne($manager, $devis3, 'ETA-003', 'Réseau de collecte eaux de ruissellement', 1, 18500.00, 'ens', '10', '3', 14);

        // Bloc 4 : VRD
        $this->addBlocHeader($manager, $devis3, 'BLOC 4 — VRD et réseaux divers', '4', 15);
        $this->addLigne($manager, $devis3, 'VRD-001', 'Réseaux EU/EP — tranchées et pose canalisations', 380, 145.00, 'ml', '20', '4', 16);
        $this->addLigne($manager, $devis3, 'VRD-002', 'Regards de visite et branchements', 12, 850.00, 'u', '20', '4', 17);
        $this->addLigne($manager, $devis3, 'VRD-003', 'Enrobé bitumineux allées d\'accès ép.8cm', 620, 48.00, 'm²', '20', '4', 18);
        $this->addLigne($manager, $devis3, 'VRD-004', 'Marquage au sol et signalétique parking', 1, 8500.00, 'ens', '20', '4', 19);
        $this->addLigne($manager, $devis3, 'VRD-005', 'Éclairage LED parking — fourniture et pose', 48, 380.00, 'u', '20', '4', 20);

        $manager->flush();

        echo "✅ Données fictives créées :\n";
        echo "   - 1 entreprise : HK Construction\n";
        echo "   - 4 clients (Vinci, Bouygues, Eiffage, SocGen Immo)\n";
        echo "   - 3 chantiers\n";
        echo "   - 3 devis (Brouillon / Envoyé 14 lignes / Accepté 20 lignes)\n";
        echo "   - Login : admin@hk.fr / admin123\n";
    }

    private function addBlocHeader(ObjectManager $manager, Devis $devis, string $designation, string $blockNumber, int $displayOrder): void
    {
        $d = new DevisDetail();
        $d->setDevis($devis);
        $d->setDesignation($designation);
        $d->setType('GROUPE');
        $d->setLineType('BH');
        $d->setIsBlockHeader(true);
        $d->setBlockNumber($blockNumber);
        $d->setDisplayOrder($displayOrder);
        $d->setIsDeleted(false);
        $manager->persist($d);
    }

    private function addLigne(ObjectManager $manager, Devis $devis, string $ref, string $designation, ?int $qte, ?float $pu, string $unite, string $tva, string $blockNumber, int $displayOrder): void
    {
        $d = new DevisDetail();
        $d->setDevis($devis);
        $d->setReference($ref);
        $d->setDesignation($designation);
        $d->setQuantite($qte);
        $d->setPrixUnitaire($pu);
        $d->setUnite($unite);
        $d->setTva($tva);
        $d->setBlockNumber($blockNumber);
        $d->setType('DETAIL');
        $d->setLineType('RL');
        $d->setIsBlockHeader(false);
        $d->setDisplayOrder($displayOrder);
        $d->setIsDeleted(false);
        if ($qte && $pu) {
            $d->setTotal((float)($qte * $pu));
        }
        $manager->persist($d);
    }
}
