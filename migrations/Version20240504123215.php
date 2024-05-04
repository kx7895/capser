<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504123215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE customer ADD h_principal_name VARCHAR(255) NOT NULL, ADD h_principal_short_name VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE customer ADD h_principal_name VARCHAR(255) DEFAULT NULL, ADD h_principal_short_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE customer SET h_principal_name = "DUMMY", h_principal_short_name = "DUMMY"');
        $this->addSql('ALTER TABLE customer MODIFY h_principal_name VARCHAR(255) NOT NULL, MODIFY h_principal_short_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP h_principal_name, DROP h_principal_short_name');
    }
}
