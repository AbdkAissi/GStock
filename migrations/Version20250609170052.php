<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609170052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_29A5EC2797AE0266 ON produit (barcode)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_historique CHANGE commentaire commentaire LONGTEXT DEFAULT NULL, CHANGE type operation_type VARCHAR(50) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_29A5EC2797AE0266 ON produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stock_historique CHANGE commentaire commentaire VARCHAR(255) DEFAULT NULL, CHANGE operation_type type VARCHAR(50) NOT NULL
        SQL);
    }
}
