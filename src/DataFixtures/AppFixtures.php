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

/**
 * Fixtures idempotentes pour onboarding HK.
 *
 * - FormeJuridique : toujours seed (SARL, SAS, EURL, SA, Auto-entrepreneur, SASU).
 * - Admin par défaut : créé seulement si aucun user n'existe.
 * - Entreprise par défaut : créée seulement si la table est vide.
 * - Clients/Chantiers/Devis démo : seulement si APP_ENV != prod et tables vides.
 *
 * Utilisation : php bin/console doctrine:fixtures:load --append
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this->seedFormesJuridiques($manager);
        $this->seedAdminUser($manager);
        $this->seedEntreprise($manager);

        // Données de démo : uniquement hors prod.
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'dev';
        if ($env !== 'prod') {
            $this->seedDemoData($manager);
        }
    }

    private function seedFormesJuridiques(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(FormeJuridique::class);
        $formes = ['SARL', 'SAS', 'EURL', 'SA', 'Auto-entrepreneur', 'SASU'];
        $added = 0;
        foreach ($formes as $nom) {
            if (!$repo->findOneBy(['nom' => $nom])) {
                $fj = new FormeJuridique();
                $fj->setNom($nom);
                $manager->persist($fj);
                $added++;
            }
        }
        if ($added > 0) {
            $manager->flush();
            echo "  + {$added} forme(s) juridique(s) ajoutée(s)\n";
        }
    }

    private function seedAdminUser(ObjectManager $manager): void
    {
        $userRepo = $manager->getRepository(User::class);
        if ($userRepo->count([]) > 0) {
            return;
        }

        $admin = new User();
        $admin->setUsername('admin@hk.fr');
        $admin->setEmail('admin@hk.fr');
        $admin->setNom('Admin');
        $admin->setPrenom('HK');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActive(true);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);
        $manager->flush();

        echo "  + user admin@hk.fr / admin123 créé\n";
    }

    private function seedEntreprise(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(Entreprise::class);
        if ($repo->count([]) > 0) {
            return;
        }

        $e = new Entreprise();
        $e->setNom('HK Entreprise');
        $e->setFormeJuridique('SARL');
        $e->setEmail('contact@hk-entreprise.fr');
        $e->setValiditeOffre('30 jours');
        $e->setDelaiExecution('À convenir');
        $e->setModeReglement('30 jours fin de mois');
        $manager->persist($e);
        $manager->flush();

        echo "  + entreprise 'HK Entreprise' créée\n";
    }

    private function seedDemoData(ObjectManager $manager): void
    {
        $clientRepo = $manager->getRepository(Client::class);
        if ($clientRepo->count([]) > 0) {
            return; // démo déjà en place
        }

        $formeRepo = $manager->getRepository(FormeJuridique::class);
        $sas = $formeRepo->findOneBy(['nom' => 'SAS']);
        $sa  = $formeRepo->findOneBy(['nom' => 'SA']);

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
            if ($fj) { $client->setFormeJuridique($fj); }
            $client->setVille($ville);
            $client->setCodePostal($cp);
            $client->setTelephone($tel);
            $client->setEmail($email);
            $manager->persist($client);
            $clients[] = $client;
        }

        $chantiersData = [
            ['CH-2026-001', 'Réhabilitation résidence Les Pins — Bâtiment A'],
            ['CH-2026-002', 'Construction bureaux Zone Technologique Nord'],
            ['CH-2026-003', 'Rénovation façades immeuble Haussmann'],
        ];
        $chantiers = [];
        foreach ($chantiersData as [$code, $nom]) {
            $chantier = new Chantier();
            $chantier->setCode($code);
            $chantier->setNom($nom);
            $chantier->setSlug(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $code)));
            $chantier->setSoftDelete(false);
            $manager->persist($chantier);
            $chantiers[] = $chantier;
        }

        $manager->flush();

        // Un devis brouillon de démo
        $devis = new Devis();
        $devis->setNumero('DEVIS202604001');
        $devis->setTitre('Devis démo — ravalement façades');
        $devis->setEtat('EtatDevisBrouillon');
        $devis->setDateCreation(new \DateTime());
        $devis->setCoefficientMateriel(1.0);
        $devis->setCoefficientMainOeuvre(1.0);
        $devis->setTauxMainOeuvre(0.0);
        if (!empty($chantiers)) {
            $devis->setChantier($chantiers[0]);
        }
        $manager->persist($devis);

        $this->addBlocHeader($manager, $devis, 'BLOC 1 — Préparation', '1', 0);
        $this->addLigne($manager, $devis, 'PRE-001', 'Installation de chantier', 1, 4500.00, 'ens', '20', '1', 1);
        $this->addLigne($manager, $devis, 'PRE-002', 'Échafaudage', 680, 12.50, 'm²', '20', '1', 2);

        $manager->flush();

        echo "  + données de démo (clients/chantiers/devis) créées\n";
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
