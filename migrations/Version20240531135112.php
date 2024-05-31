<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240531135112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice_payment (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, currency_id INT NOT NULL, accounting_plan_ledger_id INT DEFAULT NULL, created_by_id INT NOT NULL, date DATE NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9FF1B2DE2989F1FD (invoice_id), INDEX IDX_9FF1B2DE38248176 (currency_id), INDEX IDX_9FF1B2DEDF1A33AB (accounting_plan_ledger_id), INDEX IDX_9FF1B2DEB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice_payment ADD CONSTRAINT FK_9FF1B2DE2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_payment ADD CONSTRAINT FK_9FF1B2DE38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE invoice_payment ADD CONSTRAINT FK_9FF1B2DEDF1A33AB FOREIGN KEY (accounting_plan_ledger_id) REFERENCES accounting_plan_ledger (id)');
        $this->addSql('ALTER TABLE invoice_payment ADD CONSTRAINT FK_9FF1B2DEB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744CEA851AC');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174440E5067');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174460E7B4A5');
        $this->addSql('DROP INDEX IDX_9065174440E5067 ON invoice');
        $this->addSql('DROP INDEX IDX_9065174460E7B4A5 ON invoice');
        $this->addSql('DROP INDEX IDX_90651744CEA851AC ON invoice');
        $this->addSql('ALTER TABLE invoice DROP payment_currency_id, DROP payment_accounting_plan_ledger_id, DROP payment_marked_by_id, DROP payment_is_paid, DROP payment_date, DROP payment_amount, DROP payment_marked_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_payment DROP FOREIGN KEY FK_9FF1B2DE2989F1FD');
        $this->addSql('ALTER TABLE invoice_payment DROP FOREIGN KEY FK_9FF1B2DE38248176');
        $this->addSql('ALTER TABLE invoice_payment DROP FOREIGN KEY FK_9FF1B2DEDF1A33AB');
        $this->addSql('ALTER TABLE invoice_payment DROP FOREIGN KEY FK_9FF1B2DEB03A8386');
        $this->addSql('DROP TABLE invoice_payment');
        $this->addSql('ALTER TABLE invoice ADD payment_currency_id INT DEFAULT NULL, ADD payment_accounting_plan_ledger_id INT DEFAULT NULL, ADD payment_marked_by_id INT DEFAULT NULL, ADD payment_is_paid TINYINT(1) DEFAULT NULL, ADD payment_date DATE DEFAULT NULL, ADD payment_amount DOUBLE PRECISION DEFAULT NULL, ADD payment_marked_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744CEA851AC FOREIGN KEY (payment_currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174440E5067 FOREIGN KEY (payment_accounting_plan_ledger_id) REFERENCES accounting_plan_ledger (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174460E7B4A5 FOREIGN KEY (payment_marked_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9065174440E5067 ON invoice (payment_accounting_plan_ledger_id)');
        $this->addSql('CREATE INDEX IDX_9065174460E7B4A5 ON invoice (payment_marked_by_id)');
        $this->addSql('CREATE INDEX IDX_90651744CEA851AC ON invoice (payment_currency_id)');
    }
}
