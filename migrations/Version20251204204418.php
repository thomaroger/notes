<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204204418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E70812469DE2');
        $this->addSql('ALTER TABLE child DROP FOREIGN KEY FK_22B3542940C1FEA7');
        $this->addSql(
            'CREATE TABLE school_class (id INT AUTO_INCREMENT NOT NULL, year VARCHAR(255) NOT NULL, level VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE year');
        $this->addSql('DROP INDEX IDX_22B3542940C1FEA7 ON child');
        $this->addSql('ALTER TABLE child CHANGE year_id school_class_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE child ADD CONSTRAINT FK_22B3542914463F54 FOREIGN KEY (school_class_id) REFERENCES school_class (id)'
        );
        $this->addSql('CREATE INDEX IDX_22B3542914463F54 ON child (school_class_id)');
        $this->addSql('DROP INDEX IDX_9775E70812469DE2 ON theme');
        $this->addSql('ALTER TABLE theme DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE child DROP FOREIGN KEY FK_22B3542914463F54');
        $this->addSql(
            'CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql(
            'CREATE TABLE year (id INT AUTO_INCREMENT NOT NULL, year VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, level VARCHAR(10) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql('DROP TABLE school_class');
        $this->addSql('DROP INDEX IDX_22B3542914463F54 ON child');
        $this->addSql('ALTER TABLE child CHANGE school_class_id year_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE child ADD CONSTRAINT FK_22B3542940C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id) ON UPDATE NO ACTION ON DELETE NO ACTION'
        );
        $this->addSql('CREATE INDEX IDX_22B3542940C1FEA7 ON child (year_id)');
        $this->addSql('ALTER TABLE theme ADD category_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE theme ADD CONSTRAINT FK_9775E70812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION'
        );
        $this->addSql('CREATE INDEX IDX_9775E70812469DE2 ON theme (category_id)');
    }
}
