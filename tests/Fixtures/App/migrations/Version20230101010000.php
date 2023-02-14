<?php

/**
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230101010000 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    $this->addSql('CREATE TABLE resource_crawler_processes (
            id INT AUTO_INCREMENT NOT NULL,
            `name` VARCHAR(1024) NOT NULL,
            PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE resource_crawler_nodes (
            id INT AUTO_INCREMENT NOT NULL,
            process_id INT NOT NULL,
            `status` ENUM("for_processing", "in_process", "processed", "ignored", "errored") NOT NULL,
            `type` ENUM("html", "img") NOT NULL,
            `uri_type` ENUM("http", "fs") NOT NULL,
            `uri_path` VARCHAR(4096) NOT NULL,
            `code` INT UNSIGNED,
            PRIMARY KEY(id),
            INDEX (`process_id`),
            INDEX (`status`)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE resource_crawler_nodes
        ADD CONSTRAINT FK_NODE_PROCESS FOREIGN KEY (process_id) REFERENCES resource_crawler_processes (id)');
  }

  public function down(Schema $schema): void
  {
    $this->addSql('ALTER TABLE resource_crawler_nodes DROP FOREIGN KEY FK_NODE_PROCESS');
    $this->addSql('DROP TABLE resource_crawler_nodes');
    $this->addSql('DROP TABLE resource_crawler_processes');
  }
}
**/
