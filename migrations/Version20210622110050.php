<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622110050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add properties isGuardCheckIp (bool) and whiteListedIpAddresses (array) to user entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD is_guard_check_ip TINYINT(1) NOT NULL, ADD white_listed_ip_addresses LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP is_guard_check_ip, DROP white_listed_ip_addresses');
    }
}
