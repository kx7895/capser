<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240515173852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE customer_note CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE payment_marked_at payment_marked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice_attachment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice_mailing CHANGE mailed_at mailed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice_note CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice_type ADD type_en VARCHAR(2) DEFAULT NULL, ADD type_fr VARCHAR(2) DEFAULT NULL, ADD type_it VARCHAR(2) DEFAULT NULL, ADD name_en VARCHAR(255) DEFAULT NULL, ADD name_fr VARCHAR(255) DEFAULT NULL, ADD name_it VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE principal CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE unit CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_note CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE principal CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE invoice CHANGE created_at created_at DATETIME NOT NULL, CHANGE payment_marked_at payment_marked_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_mailing CHANGE mailed_at mailed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE invoice_attachment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE unit CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE invoice_note CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE customer CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_type DROP type_en, DROP type_fr, DROP type_it, DROP name_en, DROP name_fr, DROP name_it');
    }
}
