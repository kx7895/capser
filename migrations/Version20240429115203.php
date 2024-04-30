<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240429115203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accounting_plan (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE accounting_plan_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, accounting_plan_id INT NOT NULL, parent_accounting_plan_group_id INT DEFAULT NULL, INDEX IDX_5D3366F7D111FFAC (accounting_plan_id), INDEX IDX_5D3366F7A5EBE07F (parent_accounting_plan_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE accounting_plan_ledger (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, accounting_plan_group_id INT NOT NULL, INDEX IDX_89C061E5ABD0D0F (accounting_plan_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE capser_package (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, valid_from DATE NOT NULL, valid_to DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE company_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, abbreviation VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, state_name VARCHAR(255) NOT NULL, alpha2 VARCHAR(2) NOT NULL, alpha3 VARCHAR(3) NOT NULL, flag_icon_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, alpha3 VARCHAR(3) NOT NULL, symbol VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, logo_path VARCHAR(255) DEFAULT NULL, address_line1 VARCHAR(255) DEFAULT NULL, address_line2 VARCHAR(255) DEFAULT NULL, address_line3 VARCHAR(255) DEFAULT NULL, address_line4 VARCHAR(255) DEFAULT NULL, vat_id VARCHAR(255) DEFAULT NULL, vat_number VARCHAR(255) DEFAULT NULL, vat_exempt_invoices_allowed TINYINT(1) DEFAULT NULL, bank_account_holder VARCHAR(255) DEFAULT NULL, bank_account_bank VARCHAR(255) DEFAULT NULL, bank_account_iban VARCHAR(255) DEFAULT NULL, bank_account_bic VARCHAR(255) DEFAULT NULL, bank_direct_authorization_number VARCHAR(255) DEFAULT NULL, bank_direct_authorization_date DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, ledger_account_number INT DEFAULT NULL, principal_id INT NOT NULL, customer_type_id INT DEFAULT NULL, address_line_country_id INT DEFAULT NULL, accounting_plan_ledger_default_id INT DEFAULT NULL, currency_default_id INT DEFAULT NULL, language_default_id INT DEFAULT NULL, term_of_payment_default_id INT DEFAULT NULL, INDEX IDX_81398E09474870EE (principal_id), INDEX IDX_81398E09D991282D (customer_type_id), INDEX IDX_81398E091362F263 (address_line_country_id), INDEX IDX_81398E091D60E40 (accounting_plan_ledger_default_id), INDEX IDX_81398E098FA04E16 (currency_default_id), INDEX IDX_81398E092C5EA030 (language_default_id), INDEX IDX_81398E09CBB43EB2 (term_of_payment_default_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer_tag (customer_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_4C7AE9759395C3F3 (customer_id), INDEX IDX_4C7AE975BAD26311 (tag_id), PRIMARY KEY(customer_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer_contact_person (id INT AUTO_INCREMENT NOT NULL, salutation VARCHAR(20) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, customer_id INT NOT NULL, INDEX IDX_3D23D23D9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer_invoice_recipient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, customer_id INT NOT NULL, INDEX IDX_19BAF7C89395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer_note (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, customer_id INT NOT NULL, INDEX IDX_9B2C5E639395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer_note_attachment (id INT AUTO_INCREMENT NOT NULL, nice_filename VARCHAR(255) NOT NULL, storage_filename VARCHAR(500) NOT NULL, customer_note_id INT NOT NULL, INDEX IDX_62D0B6113A30ACEB (customer_note_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE customer_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, number INT DEFAULT NULL, period_from DATE NOT NULL, period_to DATE NOT NULL, due DATE NOT NULL, intro_text LONGTEXT DEFAULT NULL, outro_text LONGTEXT DEFAULT NULL, vat_type VARCHAR(3) DEFAULT NULL, vat_rate DOUBLE PRECISION NOT NULL, amount_net DOUBLE PRECISION DEFAULT NULL, amount_gross DOUBLE PRECISION DEFAULT NULL, costcenter_external VARCHAR(255) DEFAULT NULL, reference_external VARCHAR(255) DEFAULT NULL, sent TINYINT(1) DEFAULT NULL, cancelled TINYINT(1) DEFAULT NULL, nice_filename VARCHAR(255) DEFAULT NULL, storage_filename VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, invoice_reference_id INT DEFAULT NULL, customer_id INT NOT NULL, principal_id INT NOT NULL, invoice_type_id INT NOT NULL, language_id INT NOT NULL, currency_id INT NOT NULL, accounting_plan_ledger_id INT DEFAULT NULL, term_of_payment_id INT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_90651744A982F1DD (invoice_reference_id), INDEX IDX_906517449395C3F3 (customer_id), INDEX IDX_90651744474870EE (principal_id), INDEX IDX_906517443795BA40 (invoice_type_id), INDEX IDX_9065174482F1BAF4 (language_id), INDEX IDX_9065174438248176 (currency_id), INDEX IDX_90651744DF1A33AB (accounting_plan_ledger_id), INDEX IDX_90651744571ACDD3 (term_of_payment_id), INDEX IDX_90651744B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_tag (invoice_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_AB78D8402989F1FD (invoice_id), INDEX IDX_AB78D840BAD26311 (tag_id), PRIMARY KEY(invoice_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_attachment (id INT AUTO_INCREMENT NOT NULL, nice_filename VARCHAR(255) NOT NULL, storage_filename VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL, invoice_id INT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_8083A9252989F1FD (invoice_id), INDEX IDX_8083A925B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_mailing (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, mailed_at DATETIME NOT NULL, invoice_id INT NOT NULL, mailed_by_id INT DEFAULT NULL, INDEX IDX_CC00078D2989F1FD (invoice_id), INDEX IDX_CC00078DBD381469 (mailed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_mailing_recipient (id INT AUTO_INCREMENT NOT NULL, email_address VARCHAR(255) NOT NULL, email_address_type VARCHAR(3) NOT NULL, invoice_mailing_id INT NOT NULL, INDEX IDX_59BF40D3C0EDDBE8 (invoice_mailing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_note (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, invoice_id INT NOT NULL, INDEX IDX_CD7898712989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_position (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, amount DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, discount DOUBLE PRECISION DEFAULT NULL, tax_rate DOUBLE PRECISION DEFAULT NULL, invoice_id INT NOT NULL, INDEX IDX_5904BEAD2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invoice_type (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(2) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, alpha2 VARCHAR(2) NOT NULL, alpha3 VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE principal (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) DEFAULT NULL, logo_path VARCHAR(255) DEFAULT NULL, address_line1 VARCHAR(255) DEFAULT NULL, address_line2 VARCHAR(255) DEFAULT NULL, address_line3 VARCHAR(255) DEFAULT NULL, address_line4 VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, fax VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, vat_id VARCHAR(255) DEFAULT NULL, vat_number VARCHAR(255) DEFAULT NULL, vat_exempt TINYINT(1) DEFAULT NULL, vat_report_calculation VARCHAR(25) DEFAULT NULL, vat_report_interval VARCHAR(25) DEFAULT NULL, footer_column1 LONGTEXT DEFAULT NULL, footer_column2 LONGTEXT DEFAULT NULL, footer_column3 LONGTEXT DEFAULT NULL, footer_column1_en LONGTEXT DEFAULT NULL, footer_column2_en LONGTEXT DEFAULT NULL, footer_column3_en LONGTEXT DEFAULT NULL, fibu_recipient_email1 VARCHAR(255) DEFAULT NULL, fibu_recipient_email2 VARCHAR(255) DEFAULT NULL, fibu_recipient_email3 VARCHAR(255) DEFAULT NULL, fibu_document_number_range VARCHAR(25) DEFAULT NULL, capser_invoice_address LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, address_line_country_id INT DEFAULT NULL, vat_company_type_id INT DEFAULT NULL, capser_package_id INT DEFAULT NULL, main_contact_id INT NOT NULL, accounting_plan_id INT DEFAULT NULL, INDEX IDX_20A08C5B1362F263 (address_line_country_id), INDEX IDX_20A08C5B5441CEBA (vat_company_type_id), INDEX IDX_20A08C5BC9C6315E (capser_package_id), INDEX IDX_20A08C5BDF595129 (main_contact_id), UNIQUE INDEX UNIQ_20A08C5BD111FFAC (accounting_plan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, principal_id INT DEFAULT NULL, INDEX IDX_389B783474870EE (principal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE term_of_payment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, due_days INT NOT NULL, text LONGTEXT NOT NULL, text_en LONGTEXT NOT NULL, principal_id INT NOT NULL, INDEX IDX_A749CD6D474870EE (principal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, profile_image_path VARCHAR(255) DEFAULT NULL, disabled TINYINT(1) DEFAULT NULL, language_id INT NOT NULL, INDEX IDX_8D93D64982F1BAF4 (language_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_principal (user_id INT NOT NULL, principal_id INT NOT NULL, INDEX IDX_204001BFA76ED395 (user_id), INDEX IDX_204001BF474870EE (principal_id), PRIMARY KEY(user_id, principal_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE accounting_plan_group ADD CONSTRAINT FK_5D3366F7D111FFAC FOREIGN KEY (accounting_plan_id) REFERENCES accounting_plan (id)');
        $this->addSql('ALTER TABLE accounting_plan_group ADD CONSTRAINT FK_5D3366F7A5EBE07F FOREIGN KEY (parent_accounting_plan_group_id) REFERENCES accounting_plan_group (id)');
        $this->addSql('ALTER TABLE accounting_plan_ledger ADD CONSTRAINT FK_89C061E5ABD0D0F FOREIGN KEY (accounting_plan_group_id) REFERENCES accounting_plan_group (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09474870EE FOREIGN KEY (principal_id) REFERENCES principal (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09D991282D FOREIGN KEY (customer_type_id) REFERENCES customer_type (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091362F263 FOREIGN KEY (address_line_country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E091D60E40 FOREIGN KEY (accounting_plan_ledger_default_id) REFERENCES accounting_plan_ledger (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E098FA04E16 FOREIGN KEY (currency_default_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E092C5EA030 FOREIGN KEY (language_default_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09CBB43EB2 FOREIGN KEY (term_of_payment_default_id) REFERENCES term_of_payment (id)');
        $this->addSql('ALTER TABLE customer_tag ADD CONSTRAINT FK_4C7AE9759395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_tag ADD CONSTRAINT FK_4C7AE975BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_contact_person ADD CONSTRAINT FK_3D23D23D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer_invoice_recipient ADD CONSTRAINT FK_19BAF7C89395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer_note ADD CONSTRAINT FK_9B2C5E639395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer_note_attachment ADD CONSTRAINT FK_62D0B6113A30ACEB FOREIGN KEY (customer_note_id) REFERENCES customer_note (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744A982F1DD FOREIGN KEY (invoice_reference_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744474870EE FOREIGN KEY (principal_id) REFERENCES principal (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517443795BA40 FOREIGN KEY (invoice_type_id) REFERENCES invoice_type (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174482F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174438248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744DF1A33AB FOREIGN KEY (accounting_plan_ledger_id) REFERENCES accounting_plan_ledger (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744571ACDD3 FOREIGN KEY (term_of_payment_id) REFERENCES term_of_payment (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invoice_tag ADD CONSTRAINT FK_AB78D8402989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_tag ADD CONSTRAINT FK_AB78D840BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_attachment ADD CONSTRAINT FK_8083A9252989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_attachment ADD CONSTRAINT FK_8083A925B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invoice_mailing ADD CONSTRAINT FK_CC00078D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_mailing ADD CONSTRAINT FK_CC00078DBD381469 FOREIGN KEY (mailed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invoice_mailing_recipient ADD CONSTRAINT FK_59BF40D3C0EDDBE8 FOREIGN KEY (invoice_mailing_id) REFERENCES invoice_mailing (id)');
        $this->addSql('ALTER TABLE invoice_note ADD CONSTRAINT FK_CD7898712989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_position ADD CONSTRAINT FK_5904BEAD2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE principal ADD CONSTRAINT FK_20A08C5B1362F263 FOREIGN KEY (address_line_country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE principal ADD CONSTRAINT FK_20A08C5B5441CEBA FOREIGN KEY (vat_company_type_id) REFERENCES company_type (id)');
        $this->addSql('ALTER TABLE principal ADD CONSTRAINT FK_20A08C5BC9C6315E FOREIGN KEY (capser_package_id) REFERENCES capser_package (id)');
        $this->addSql('ALTER TABLE principal ADD CONSTRAINT FK_20A08C5BDF595129 FOREIGN KEY (main_contact_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE principal ADD CONSTRAINT FK_20A08C5BD111FFAC FOREIGN KEY (accounting_plan_id) REFERENCES accounting_plan (id)');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B783474870EE FOREIGN KEY (principal_id) REFERENCES principal (id)');
        $this->addSql('ALTER TABLE term_of_payment ADD CONSTRAINT FK_A749CD6D474870EE FOREIGN KEY (principal_id) REFERENCES principal (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D64982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE user_principal ADD CONSTRAINT FK_204001BFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_principal ADD CONSTRAINT FK_204001BF474870EE FOREIGN KEY (principal_id) REFERENCES principal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE accounting_plan_group DROP FOREIGN KEY FK_5D3366F7D111FFAC');
        $this->addSql('ALTER TABLE accounting_plan_group DROP FOREIGN KEY FK_5D3366F7A5EBE07F');
        $this->addSql('ALTER TABLE accounting_plan_ledger DROP FOREIGN KEY FK_89C061E5ABD0D0F');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09474870EE');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09D991282D');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091362F263');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E091D60E40');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E098FA04E16');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E092C5EA030');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09CBB43EB2');
        $this->addSql('ALTER TABLE customer_tag DROP FOREIGN KEY FK_4C7AE9759395C3F3');
        $this->addSql('ALTER TABLE customer_tag DROP FOREIGN KEY FK_4C7AE975BAD26311');
        $this->addSql('ALTER TABLE customer_contact_person DROP FOREIGN KEY FK_3D23D23D9395C3F3');
        $this->addSql('ALTER TABLE customer_invoice_recipient DROP FOREIGN KEY FK_19BAF7C89395C3F3');
        $this->addSql('ALTER TABLE customer_note DROP FOREIGN KEY FK_9B2C5E639395C3F3');
        $this->addSql('ALTER TABLE customer_note_attachment DROP FOREIGN KEY FK_62D0B6113A30ACEB');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744A982F1DD');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449395C3F3');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744474870EE');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517443795BA40');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174482F1BAF4');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174438248176');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744DF1A33AB');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744571ACDD3');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744B03A8386');
        $this->addSql('ALTER TABLE invoice_tag DROP FOREIGN KEY FK_AB78D8402989F1FD');
        $this->addSql('ALTER TABLE invoice_tag DROP FOREIGN KEY FK_AB78D840BAD26311');
        $this->addSql('ALTER TABLE invoice_attachment DROP FOREIGN KEY FK_8083A9252989F1FD');
        $this->addSql('ALTER TABLE invoice_attachment DROP FOREIGN KEY FK_8083A925B03A8386');
        $this->addSql('ALTER TABLE invoice_mailing DROP FOREIGN KEY FK_CC00078D2989F1FD');
        $this->addSql('ALTER TABLE invoice_mailing DROP FOREIGN KEY FK_CC00078DBD381469');
        $this->addSql('ALTER TABLE invoice_mailing_recipient DROP FOREIGN KEY FK_59BF40D3C0EDDBE8');
        $this->addSql('ALTER TABLE invoice_note DROP FOREIGN KEY FK_CD7898712989F1FD');
        $this->addSql('ALTER TABLE invoice_position DROP FOREIGN KEY FK_5904BEAD2989F1FD');
        $this->addSql('ALTER TABLE principal DROP FOREIGN KEY FK_20A08C5B1362F263');
        $this->addSql('ALTER TABLE principal DROP FOREIGN KEY FK_20A08C5B5441CEBA');
        $this->addSql('ALTER TABLE principal DROP FOREIGN KEY FK_20A08C5BC9C6315E');
        $this->addSql('ALTER TABLE principal DROP FOREIGN KEY FK_20A08C5BDF595129');
        $this->addSql('ALTER TABLE principal DROP FOREIGN KEY FK_20A08C5BD111FFAC');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B783474870EE');
        $this->addSql('ALTER TABLE term_of_payment DROP FOREIGN KEY FK_A749CD6D474870EE');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64982F1BAF4');
        $this->addSql('ALTER TABLE user_principal DROP FOREIGN KEY FK_204001BFA76ED395');
        $this->addSql('ALTER TABLE user_principal DROP FOREIGN KEY FK_204001BF474870EE');
        $this->addSql('DROP TABLE accounting_plan');
        $this->addSql('DROP TABLE accounting_plan_group');
        $this->addSql('DROP TABLE accounting_plan_ledger');
        $this->addSql('DROP TABLE capser_package');
        $this->addSql('DROP TABLE company_type');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE customer_tag');
        $this->addSql('DROP TABLE customer_contact_person');
        $this->addSql('DROP TABLE customer_invoice_recipient');
        $this->addSql('DROP TABLE customer_note');
        $this->addSql('DROP TABLE customer_note_attachment');
        $this->addSql('DROP TABLE customer_type');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_tag');
        $this->addSql('DROP TABLE invoice_attachment');
        $this->addSql('DROP TABLE invoice_mailing');
        $this->addSql('DROP TABLE invoice_mailing_recipient');
        $this->addSql('DROP TABLE invoice_note');
        $this->addSql('DROP TABLE invoice_position');
        $this->addSql('DROP TABLE invoice_type');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE principal');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE term_of_payment');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_principal');
    }
}
