<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210728124623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fields discord_id and discord_username. Update field account_must_be_verified_before is datetime_immutable or null';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD discord_id VARCHAR(255) DEFAULT NULL, ADD discord_username VARCHAR(255) DEFAULT NULL, CHANGE account_must_be_verified_before account_must_be_verified_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP discord_id, DROP discord_username, CHANGE account_must_be_verified_before account_must_be_verified_before DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
