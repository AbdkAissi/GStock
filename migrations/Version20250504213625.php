<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504213625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, fournisseur_id INT NOT NULL, date DATETIME NOT NULL, montant DOUBLE PRECISION NOT NULL, moyen_paiment VARCHAR(10) NOT NULL, type VARCHAR(10) NOT NULL, INDEX IDX_B1DC7A1E19EB6921 (client_id), INDEX IDX_B1DC7A1E670C757F (fournisseur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E670C757F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE paiement
        SQL);
    }
}
