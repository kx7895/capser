<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240505140610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unit (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, string_en VARCHAR(255) DEFAULT NULL, name_fr VARCHAR(255) DEFAULT NULL, string_it VARCHAR(255) DEFAULT NULL, disabled TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, principal_id INT DEFAULT NULL, created_by_id INT NOT NULL, updated_by_id INT DEFAULT NULL, INDEX IDX_DCBB0C53474870EE (principal_id), INDEX IDX_DCBB0C53B03A8386 (created_by_id), INDEX IDX_DCBB0C53896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53474870EE FOREIGN KEY (principal_id) REFERENCES principal (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C53896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE customer CHANGE h_principal_short_name h_principal_short_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53474870EE');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53B03A8386');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C53896DBBDE');
        $this->addSql('DROP TABLE unit');
        $this->addSql('ALTER TABLE customer CHANGE h_principal_short_name h_principal_short_name VARCHAR(255) NOT NULL');
    }
}
