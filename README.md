# resource-crawler-bundle

Symfony bundle for crawling disk/web resource.  
Spider bot can navigate other disk or web resources.  
Internet bot can be customized by path mask and other options.  
Crawler scan HTML-document extract hyperlinks and push them to the index pool of next iteration.

## Install

`composer require andrew-svirin/resource-crawler-bundle:dev-main`

## Usage

```php
    // Resolve service by alias or by class.
    $resourceCrawler = $this->getContainer()->get('resource_crawler.crawler');

    $url       = 'https://site.com/index.html';
    $pathMasks = ['+site.com/', '-embed'];

    // Do one crawl iteration.
    $task = $resourceCrawler->crawlWebResource($url, $pathMasks);

    // Reset all crawling related data.
    $resourceCrawler->resetWebResource($url);
```

## Development

1. `make build` to prepare infrastructure
2. `make stasrt` to start infrastructure
3. `make install` to install dependencies

TODO:

- Add DbProcessStore
- Customize path selector rule (randomizer).
- Customize user agent rule (generator).
- Fix inline images.
- Handle href for `mailto:`, `tel:`, `#section`, `javascript:`, `file:`
