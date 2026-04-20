<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne created_at à la table user (idempotent).
 */
final class Version20260420150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at column to user table';
    }

    public function up(Schema $schema): void
    {
        $exists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND COLUMN_NAME = 'created_at'"
        );
        if ($exists === 0) {
            $this->addSql("ALTER TABLE user ADD created_at DATETIME DEFAULT NULL");
            // Pour les users existants, mettre une date proche pour éviter NULL.
            $this->addSql("UPDATE user SET created_at = NOW() WHERE created_at IS NULL");
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user DROP COLUMN created_at");
    }
}
