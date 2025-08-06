<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521195318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE preference DROP FOREIGN KEY FK_5D69B053A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5D69B053A76ED395 ON preference
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preference ADD fumeur TINYINT(1) NOT NULL, ADD animaux TINYINT(1) NOT NULL, ADD musique TINYINT(1) NOT NULL, ADD autres LONGTEXT DEFAULT NULL, DROP cle, DROP valeur, CHANGE user_id utilisateur_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preference ADD CONSTRAINT FK_5D69B053FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5D69B053FB88E14F ON preference (utilisateur_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE preference DROP FOREIGN KEY FK_5D69B053FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_5D69B053FB88E14F ON preference
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preference ADD cle VARCHAR(100) NOT NULL, ADD valeur VARCHAR(255) NOT NULL, DROP fumeur, DROP animaux, DROP musique, DROP autres, CHANGE utilisateur_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE preference ADD CONSTRAINT FK_5D69B053A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5D69B053A76ED395 ON preference (user_id)
        SQL);
    }
}
