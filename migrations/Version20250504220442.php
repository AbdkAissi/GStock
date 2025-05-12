<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504220442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD solde DOUBLE PRECISION NOT NULL, ADD credit DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fournisseur ADD solde DOUBLE PRECISION NOT NULL, ADD credit VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP solde, DROP credit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fournisseur DROP solde, DROP credit
        SQL);
    }
}
