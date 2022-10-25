<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Document;

use AndrewSvirin\ResourceCrawlerBundle\Document\Html\HtmlExtractor;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\Node;
use DOMDocument;

/**
 * Manager for document domain.
 *
 * @interal
 */
final class DocumentManager
{
    public function __construct(
        private readonly HtmlExtractor $htmlExtractor,
        private readonly DocumentFactory $documentFactory
    ) {
    }

    public function createDocument(Node $node): DOMDocument
    {
        return $this->documentFactory->create($node->getContent());
    }

    public function extractAHrefs(Node $node): iterable
    {
        return $this->htmlExtractor->extractAHrefs($node->getDocument());
    }

    public function extractImgSrcs(Node $node): iterable
    {
        return $this->htmlExtractor->extractImgSrcs($node->getDocument());
    }
}
