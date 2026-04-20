<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260420091800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create missing facture_ligne table (entity was defined but migration never ran)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS facture_ligne (id INT AUTO_INCREMENT NOT NULL, designation LONGTEXT DEFAULT NULL, quantite INT DEFAULT NULL, prix_unitaire_ht NUMERIC(10, 2) DEFAULT NULL, taux_tva NUMERIC(5, 2) DEFAULT '20.00' NOT NULL, facture_id INT NOT NULL, INDEX IDX_C5C453347F2DEE08 (facture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`");

        $fkExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'facture_ligne' AND CONSTRAINT_NAME = 'FK_C5C453347F2DEE08'"
        );
        if ($fkExists === 0) {
            $this->addSql('ALTER TABLE facture_ligne ADD CONSTRAINT FK_C5C453347F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE facture_ligne DROP FOREIGN KEY FK_C5C453347F2DEE08');
        $this->addSql('DROP TABLE facture_ligne');
    }
}
