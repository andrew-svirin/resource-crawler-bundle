# resource-crawler-bundle

Symfony bundle for crawling disk/web resource.  
Spider bot can navigate other disk or web resources.  
Internet bot can be customized by path mask and other options.  
Crawler scan HTML-document extract hyperlinks and push them to the index pool of next iteration.

## Install

`composer require andrew-svirin/resource-crawler-bundle:dev-main`

## Usage

```php

    use \AndrewSvirin\ResourceCrawlerBundle\Crawler\Ref\RefPath;  
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
      public function call(RefPath $refPath, CrawlingTask $task): void
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

TODO:

- Add DbProcessStore
- Customize path selector rule (randomizer).
- Customize user agent rule (generator).
