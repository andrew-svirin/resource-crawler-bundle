# resource-crawler-bundle

Symfony bundle for crawling disk/web resource.  
Spider bot can navigate other disk or web resources.  
Internet bot can be customized by path mask and other options.  
Crawler scan HTML-document extract hyperlinks and push them to the index pool of next iteration.

## Install

`composer require andrew-svirin/resource-crawler-bundle:dev-main`

Add to `doctrine.yaml` to avoid table to be associated with entities.
```
doctrine:
    dbal:
        schema_filter: ~^(?!resource_crawler_)~
```

Add to `resource_crawler.yaml` to avoid table to be associated with entities.
```
resource_crawler:
  process:
    is_lockable: true
    store: 'resource_crawler.process_db_store'
#    store: 'resource_crawler.process_file_store'
    file_store:
      dir: "%kernel.project_dir%/storage/saver"
```

Add migration `Version20230101010000.php`:
```php
<?php

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
```

## Usage

```php

    use \AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\Ref;  
    use \AndrewSvirin\ResourceCrawlerBundle\Crawler\RefHandlerClosureInterface;  
    use \AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;  
    
    /* @var $resourceCrawler \AndrewSvirin\ResourceCrawlerBundle\Crawler\ResourceCrawler */
    
    // Resolve service by alias or by class.
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url = 'https://site.com/index.html';
    $pathMasks = ['+site.com/', '-embed'];
    $substitutionRules = [
      ['/(#other-anchor)/i', ''], // remove anchor `other-anchor`
      ['/(\?.*)([&*]h=[^&#]*)(.*)/i', '$1$3'], // remove query param `h`
      ['/(\?.*)([&*]w=[^&#]*)(.*)/i', '$1$3'], // remove query param `w`
    ];
    $op = new class() implements RefHandlerClosureInterface {
      public function call(Ref $ref, CrawlingTask $task): void
      {
        // Here is possible to handle reference in task node.
      }
    };

    // Do one of multiple crawl iteration.
    $task = $resourceCrawler->crawlWebResource($url, $pathMasks, $substitutionRules, $op);
    
    // Take analyze of resource crawling.
    $analyze = $resourceCrawler->analyzeCrawlingWebResource($url);
    
    if($someExceptionCondition){
        // Move task back for be crawled again.
        $resourceCrawler->rollbackTask($task);
    }

    // Reset all crawling related data.
    $resourceCrawler->resetWebResource($url);
```

## Development

1. `make build` to prepare infrastructure
2. `make stasrt` to start infrastructure
3. `make install` to install dependencies
4. Run debug test: `make xdebug filter=value`
