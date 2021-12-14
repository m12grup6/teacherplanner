<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211201091222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A591CC992 ON subject (course_id)');
        $this->addSql('ALTER TABLE user ADD teaching_hours INT DEFAULT NULL, ADD teacher_constraints JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subject DROP FOREIGN KEY FK_FBCE3E7A591CC992');
        $this->addSql('DROP INDEX IDX_FBCE3E7A591CC992 ON subject');
        $this->addSql('ALTER TABLE user DROP teaching_hours, DROP teacher_constraints');
    }
}
