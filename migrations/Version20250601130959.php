<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601130959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit ADD barcode VARCHAR(255) DEFAULT NULL');

        // Remplir temporairement les barcode avec une valeur unique
        $produits = $this->connection->fetchAllAssociative('SELECT id FROM produit');
        foreach ($produits as $produit) {
            $code = uniqid('prod_');
            $this->connection->executeStatement('UPDATE produit SET barcode = ? WHERE id = ?', [$code, $produit['id']]);
        }

        // Maintenant on peut ajouter la contrainte UNIQUE
        $this->addSql('ALTER TABLE produit ADD UNIQUE INDEX UNIQ_29A5EC2797AE0266 (barcode)');
    }


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_29A5EC2797AE0266 ON produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP barcode
        SQL);
    }
}
