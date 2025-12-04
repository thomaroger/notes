<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204212110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assessment ADD theme_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE assessment ADD CONSTRAINT FK_F7523D7059027487 FOREIGN KEY (theme_id) REFERENCES theme (id)'
        );
        $this->addSql('CREATE INDEX IDX_F7523D7059027487 ON assessment (theme_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assessment DROP FOREIGN KEY FK_F7523D7059027487');
        $this->addSql('DROP INDEX IDX_F7523D7059027487 ON assessment');
        $this->addSql('ALTER TABLE assessment DROP theme_id');
    }
}
