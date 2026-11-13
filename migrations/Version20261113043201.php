<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261113043201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, customer_name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, contact_number VARCHAR(20) NOT NULL, check_in DATE NOT NULL, check_out DATE NOT NULL, total_price NUMERIC(10, 2) NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, room_id INT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_E00CEDDE54177093 (room_id), INDEX IDX_E00CEDDEB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE54177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE54177093');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEB03A8386');
        $this->addSql('DROP TABLE booking');
    }
}
