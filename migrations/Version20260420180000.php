<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Révision de prix BTP :
 *   - nouvelle table indice_btp (type, mois, valeur, createdAt)
 *   - colonnes prix_revisable, indice_type, indice_base_mois sur chantier
 */
final class Version20260420180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indice_btp table + revision price columns on chantier';
    }

    public function up(Schema $schema): void
    {
        // Table indice_btp
        $tbl = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'indice_btp'"
        );
        if ($tbl === 0) {
            $this->addSql('CREATE TABLE indice_btp (
                id INT AUTO_INCREMENT NOT NULL,
                type VARCHAR(20) NOT NULL,
                mois DATE NOT NULL,
                valeur NUMERIC(10, 4) NOT NULL,
                created_at DATETIME DEFAULT NULL,
                UNIQUE INDEX uq_indice_type_mois (type, mois),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Colonnes chantier
        $cols = [
            'prix_revisable'   => "ADD prix_revisable TINYINT(1) NOT NULL DEFAULT 0",
            'indice_type'      => "ADD indice_type VARCHAR(20) DEFAULT NULL",
            'indice_base_mois' => "ADD indice_base_mois DATE DEFAULT NULL",
        ];
        foreach ($cols as $colName => $ddl) {
            $exists = (int) $this->connection->fetchOne(
                "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'chantier' AND COLUMN_NAME = '$colName'"
            );
            if ($exists === 0) {
                $this->addSql("ALTER TABLE chantier $ddl");
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE chantier DROP COLUMN prix_revisable");
        $this->addSql("ALTER TABLE chantier DROP COLUMN indice_type");
        $this->addSql("ALTER TABLE chantier DROP COLUMN indice_base_mois");
        $this->addSql("DROP TABLE indice_btp");
    }
}
