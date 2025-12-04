<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204204821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD school_class_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE user ADD CONSTRAINT FK_8D93D64914463F54 FOREIGN KEY (school_class_id) REFERENCES school_class (id)'
        );
        $this->addSql('CREATE INDEX IDX_8D93D64914463F54 ON user (school_class_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64914463F54');
        $this->addSql('DROP INDEX IDX_8D93D64914463F54 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP school_class_id');
    }
}
