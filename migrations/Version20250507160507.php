<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507160507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD commande_achat_id INT DEFAULT NULL, ADD commande_vente_id INT DEFAULT NULL, DROP type_destinataire, CHANGE client_id client_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E28B5C98D FOREIGN KEY (commande_achat_id) REFERENCES commande_achat (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1EABE70F90 FOREIGN KEY (commande_vente_id) REFERENCES commande_vente (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B1DC7A1E28B5C98D ON paiement (commande_achat_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B1DC7A1EABE70F90 ON paiement (commande_vente_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E28B5C98D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1EABE70F90
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B1DC7A1E28B5C98D ON paiement
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B1DC7A1EABE70F90 ON paiement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE paiement ADD type_destinataire VARCHAR(50) NOT NULL, DROP commande_achat_id, DROP commande_vente_id, CHANGE client_id client_id INT NOT NULL
        SQL);
    }
}
