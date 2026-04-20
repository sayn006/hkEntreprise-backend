<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne is_dgd (Décompte Général Définitif) à facture_situation.
 */
final class Version20260420170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_dgd column to facture_situation';
    }

    public function up(Schema $schema): void
    {
        $exists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'facture_situation' AND COLUMN_NAME = 'is_dgd'"
        );
        if ($exists === 0) {
            $this->addSql("ALTER TABLE facture_situation ADD is_dgd TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE facture_situation DROP COLUMN is_dgd");
    }
}
