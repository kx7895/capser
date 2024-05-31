<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240530131216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE preference (id INT AUTO_INCREMENT NOT NULL, setting VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, name_fr VARCHAR(255) DEFAULT NULL, name_it VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, description_en LONGTEXT DEFAULT NULL, description_fr LONGTEXT DEFAULT NULL, description_it LONGTEXT DEFAULT NULL, public_setting TINYINT(1) DEFAULT NULL, default_value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE preference');
    }
}
