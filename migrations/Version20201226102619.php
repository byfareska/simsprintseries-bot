<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201226102619 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assetto_corsa_gave_rank (id INT AUTO_INCREMENT NOT NULL, instance_id INT NOT NULL, driver_id INT NOT NULL, INDEX IDX_F2C233E63A51721D (instance_id), INDEX IDX_F2C233E6C3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assetto_corsa_gave_rank ADD CONSTRAINT FK_F2C233E63A51721D FOREIGN KEY (instance_id) REFERENCES assetto_corsa_active_event (id)');
        $this->addSql('ALTER TABLE assetto_corsa_gave_rank ADD CONSTRAINT FK_F2C233E6C3423909 FOREIGN KEY (driver_id) REFERENCES assetto_corsa_associated_name (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE assetto_corsa_gave_rank');
    }
}
