<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240320174307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_myr ADD game_myr_id INT NOT NULL');
        $this->addSql('ALTER TABLE player_myr ADD CONSTRAINT FK_829912EDD469E664 FOREIGN KEY (game_myr_id) REFERENCES game_myr (id)');
        $this->addSql('CREATE INDEX IDX_829912EDD469E664 ON player_myr (game_myr_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player_myr DROP FOREIGN KEY FK_829912EDD469E664');
        $this->addSql('DROP INDEX IDX_829912EDD469E664 ON player_myr');
        $this->addSql('ALTER TABLE player_myr DROP game_myr_id');
    }
}
