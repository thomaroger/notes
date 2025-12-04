<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204210704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE assessment (id INT AUTO_INCREMENT NOT NULL, school_class_id INT NOT NULL, title VARCHAR(255) NOT NULL, max_score INT NOT NULL, INDEX IDX_F7523D7014463F54 (school_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, assessment_id INT NOT NULL, score DOUBLE PRECISION NOT NULL, INDEX IDX_32993751DD62C21B (child_id), INDEX IDX_32993751DD3DD5F1 (assessment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE assessment ADD CONSTRAINT FK_F7523D7014463F54 FOREIGN KEY (school_class_id) REFERENCES school_class (id)'
        );
        $this->addSql(
            'ALTER TABLE score ADD CONSTRAINT FK_32993751DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)'
        );
        $this->addSql(
            'ALTER TABLE score ADD CONSTRAINT FK_32993751DD3DD5F1 FOREIGN KEY (assessment_id) REFERENCES assessment (id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assessment DROP FOREIGN KEY FK_F7523D7014463F54');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751DD62C21B');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751DD3DD5F1');
        $this->addSql('DROP TABLE assessment');
        $this->addSql('DROP TABLE score');
    }
}
