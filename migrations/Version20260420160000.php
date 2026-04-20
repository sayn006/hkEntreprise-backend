<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drop de la table `situation` (entité legacy, remplacée par facture_situation).
 * La table `situation_commentaires` reste (utilisée par FactureSituation).
 */
final class Version20260420160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop legacy situation table (replaced by facture_situation)';
    }

    public function up(Schema $schema): void
    {
        // Check si la table existe avant de dropper (idempotent)
        $exists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'situation'"
        );
        if ($exists > 0) {
            $this->addSql('DROP TABLE situation');
        }
    }

    public function down(Schema $schema): void
    {
        // Recréation minimale (pour rollback). Champs basés sur l'ancienne entité Situation.
        $this->addSql('CREATE TABLE situation (
            id INT AUTO_INCREMENT NOT NULL,
            facture_id INT NOT NULL,
            numero INT NOT NULL,
            date_debut_periode DATE NOT NULL,
            date_fin_periode DATE NOT NULL,
            montant_travaux DOUBLE PRECISION NOT NULL,
            retenue_garantie DOUBLE PRECISION NOT NULL,
            montant_tva DOUBLE PRECISION NOT NULL,
            prorata TINYINT(1) NOT NULL,
            prorata_percent DOUBLE PRECISION DEFAULT NULL,
            montant_prorata DOUBLE PRECISION DEFAULT NULL,
            montant_ht DOUBLE PRECISION NOT NULL,
            montant_ttc DOUBLE PRECISION NOT NULL,
            retenue_garantie_percent DOUBLE PRECISION DEFAULT NULL,
            tva_percent DOUBLE PRECISION DEFAULT NULL,
            titre VARCHAR(255) DEFAULT NULL,
            INDEX IDX_SITUATION_FACTURE (facture_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }
}
