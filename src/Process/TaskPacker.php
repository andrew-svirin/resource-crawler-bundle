<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HtmlNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\ImgNode;
use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\UriFactory;
use AndrewSvirin\ResourceCrawlerBundle\Resource\UriInterface;
use LogicException;

/**
 * Packer for the task.
 *
 * @interal
 */
final class TaskPacker
{
    private const NODE_TYPE_HTML = 'html';

    private const NODE_TYPE_IMG = 'img';

    private const URI_TYPE_HTTP = 'http';

    private const URI_TYPE_FS = 'fs';

    public function __construct(
        private readonly UriFactory $uriFactory,
        private readonly NodeFactory $nodeFactory,
    ) {
    }

    public function packNodeType(NodeInterface $node): string
    {
        if ($node instanceof HtmlNode) {
            $nodeType = self::NODE_TYPE_HTML;
        } elseif ($node instanceof ImgNode) {
            $nodeType = self::NODE_TYPE_IMG;
        } else {
            throw new LogicException('Incorrect node type.');
        }

        return $nodeType;
    }

    public function packUriType(UriInterface $uri): string
    {
        if ($uri instanceof HttpUri) {
            $uriType = self::URI_TYPE_HTTP;
        } elseif ($uri instanceof FsUri) {
            $uriType = self::URI_TYPE_FS;
        } else {
            throw new LogicException('Incorrect uri type.');
        }

        return $uriType;
    }

    public function unpackNode(string $type, UriInterface $uri): NodeInterface
    {
        if (self::NODE_TYPE_HTML === $type) {
            $node = $this->nodeFactory->createHtml($uri);
        } elseif (self::NODE_TYPE_IMG === $type) {
            $node = $this->nodeFactory->createImg($uri);
        } else {
            throw new LogicException('Incorrect node type.');
        }

        return $node;
    }

    public function unpackUri(string $type, string $path): UriInterface
    {
        if (self::URI_TYPE_HTTP === $type) {
            $uri = $this->uriFactory->createHttp($path);
        } elseif (self::URI_TYPE_FS === $type) {
            $uri = $this->uriFactory->createFs($path);
        } else {
            throw new LogicException('Incorrect uri type.');
        }

        return $uri;
    }
}
