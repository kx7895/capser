<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240505140921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit ADD name_en VARCHAR(255) DEFAULT NULL, ADD name_it VARCHAR(255) DEFAULT NULL, DROP string_en, DROP string_it');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit ADD string_en VARCHAR(255) DEFAULT NULL, ADD string_it VARCHAR(255) DEFAULT NULL, DROP name_en, DROP name_it');
    }
}
