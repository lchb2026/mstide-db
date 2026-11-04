<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023223939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE compounds_types (compound_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_9F4AFA9AAE76FC6F (compound_id), INDEX IDX_9F4AFA9AC54C8C93 (type_id), PRIMARY KEY(compound_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compounds_types ADD CONSTRAINT FK_9F4AFA9AAE76FC6F FOREIGN KEY (compound_id) REFERENCES compounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compounds_types ADD CONSTRAINT FK_9F4AFA9AC54C8C93 FOREIGN KEY (type_id) REFERENCES types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fragments_types DROP FOREIGN KEY FK_A2B66AF7596BD57E');
        $this->addSql('ALTER TABLE fragments_types DROP FOREIGN KEY FK_A2B66AF7C54C8C93');
        $this->addSql('DROP TABLE fragments_types');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fragments_types (fragment_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_9CC6BCAC596BD57E (fragment_id), INDEX IDX_9CC6BCACC54C8C93 (type_id), PRIMARY KEY(fragment_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE fragments_types ADD CONSTRAINT FK_A2B66AF7596BD57E FOREIGN KEY (fragment_id) REFERENCES fragments (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fragments_types ADD CONSTRAINT FK_A2B66AF7C54C8C93 FOREIGN KEY (type_id) REFERENCES types (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE compounds_types DROP FOREIGN KEY FK_9F4AFA9AAE76FC6F');
        $this->addSql('ALTER TABLE compounds_types DROP FOREIGN KEY FK_9F4AFA9AC54C8C93');
        $this->addSql('DROP TABLE compounds_types');
    }
}
