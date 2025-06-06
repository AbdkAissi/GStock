<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521221024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction du champ etat_paiement pour qu\'il ne soit plus nullable';
    }

    public function up(Schema $schema): void
    {
        // Mettre à jour tous les enregistrements avec etat_paiement NULL ou vide
        $this->addSql("UPDATE paiement SET etat_paiement = 'en_attente' WHERE etat_paiement IS NULL OR etat_paiement = ''");

        // Modifier la colonne pour qu'elle ne soit plus nullable
        $this->addSql('ALTER TABLE paiement MODIFY etat_paiement VARCHAR(20) NOT NULL DEFAULT \'en_attente\'');
    }

    public function down(Schema $schema): void
    {
        // Revenir à l'état précédent (nullable)
        $this->addSql('ALTER TABLE paiement MODIFY etat_paiement VARCHAR(20) DEFAULT NULL');
    }
}
