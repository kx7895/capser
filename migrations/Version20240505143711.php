<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240505143711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_position ADD unit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_position ADD CONSTRAINT FK_5904BEADF8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id)');
        $this->addSql('CREATE INDEX IDX_5904BEADF8BD700D ON invoice_position (unit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_position DROP FOREIGN KEY FK_5904BEADF8BD700D');
        $this->addSql('DROP INDEX IDX_5904BEADF8BD700D ON invoice_position');
        $this->addSql('ALTER TABLE invoice_position DROP unit_id');
    }
}
