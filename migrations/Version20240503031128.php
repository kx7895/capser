<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503031128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD h_customer_name VARCHAR(255) NOT NULL, ADD h_customer_short_name VARCHAR(255) DEFAULT NULL, ADD h_principal_name VARCHAR(255) NOT NULL, ADD h_principal_short_name VARCHAR(255) DEFAULT NULL, ADD payment_is_paid TINYINT(1) DEFAULT NULL, ADD payment_date DATE DEFAULT NULL, ADD payment_amount DOUBLE PRECISION DEFAULT NULL, ADD payment_market_at DATETIME DEFAULT NULL, ADD payment_currency_id INT DEFAULT NULL, ADD payment_accounting_plan_ledger_id INT DEFAULT NULL, ADD payment_marked_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744CEA851AC FOREIGN KEY (payment_currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174440E5067 FOREIGN KEY (payment_accounting_plan_ledger_id) REFERENCES accounting_plan_ledger (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174460E7B4A5 FOREIGN KEY (payment_marked_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_90651744CEA851AC ON invoice (payment_currency_id)');
        $this->addSql('CREATE INDEX IDX_9065174440E5067 ON invoice (payment_accounting_plan_ledger_id)');
        $this->addSql('CREATE INDEX IDX_9065174460E7B4A5 ON invoice (payment_marked_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744CEA851AC');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174440E5067');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174460E7B4A5');
        $this->addSql('DROP INDEX IDX_90651744CEA851AC ON invoice');
        $this->addSql('DROP INDEX IDX_9065174440E5067 ON invoice');
        $this->addSql('DROP INDEX IDX_9065174460E7B4A5 ON invoice');
        $this->addSql('ALTER TABLE invoice DROP h_customer_name, DROP h_customer_short_name, DROP h_principal_name, DROP h_principal_short_name, DROP payment_is_paid, DROP payment_date, DROP payment_amount, DROP payment_market_at, DROP payment_currency_id, DROP payment_accounting_plan_ledger_id, DROP payment_marked_by_id');
    }
}
