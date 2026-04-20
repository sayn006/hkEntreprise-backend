<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les colonnes email_* à la table entreprise pour permettre un expéditeur
 * par défaut + un override par type de document (devis, facture, avancement,
 * situation, bon_commande).
 */
final class Version20260420120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email_default + email_{devis,facture,avancement,situation,bon_commande} columns to entreprise';
    }

    public function up(Schema $schema): void
    {
        $cols = [
            'email_default'      => "VARCHAR(255) DEFAULT NULL",
            'email_devis'        => "VARCHAR(255) DEFAULT NULL",
            'email_facture'      => "VARCHAR(255) DEFAULT NULL",
            'email_avancement'   => "VARCHAR(255) DEFAULT NULL",
            'email_situation'    => "VARCHAR(255) DEFAULT NULL",
            'email_bon_commande' => "VARCHAR(255) DEFAULT NULL",
        ];

        foreach ($cols as $name => $type) {
            $exists = (int) $this->connection->fetchOne(
                "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'entreprise' AND COLUMN_NAME = :c",
                ['c' => $name]
            );
            if ($exists === 0) {
                $this->addSql("ALTER TABLE entreprise ADD $name $type");
            }
        }
    }

    public function down(Schema $schema): void
    {
        $cols = ['email_default','email_devis','email_facture','email_avancement','email_situation','email_bon_commande'];
        foreach ($cols as $c) {
            $this->addSql("ALTER TABLE entreprise DROP COLUMN $c");
        }
    }
}
