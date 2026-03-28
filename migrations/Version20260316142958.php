<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316142958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis_avancement CHANGE total_ht total_ht NUMERIC(12, 2) DEFAULT 0 NOT NULL, CHANGE total_cumule total_cumule NUMERIC(12, 2) DEFAULT 0 NOT NULL, CHANGE pourcentage_global pourcentage_global NUMERIC(5, 2) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE devis_avancement_detail CHANGE pourcentage_moins1 pourcentage_moins1 NUMERIC(5, 2) DEFAULT 0 NOT NULL, CHANGE total_htmoins1 total_htmoins1 NUMERIC(12, 2) DEFAULT 0 NOT NULL, CHANGE pourcentage pourcentage NUMERIC(5, 2) DEFAULT 0 NOT NULL, CHANGE total_ht total_ht NUMERIC(12, 2) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devis_avancement CHANGE total_ht total_ht NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, CHANGE total_cumule total_cumule NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, CHANGE pourcentage_global pourcentage_global NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE devis_avancement_detail CHANGE pourcentage_moins1 pourcentage_moins1 NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, CHANGE total_htmoins1 total_htmoins1 NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, CHANGE pourcentage pourcentage NUMERIC(5, 2) DEFAULT \'0.00\' NOT NULL, CHANGE total_ht total_ht NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL');
    }
}
