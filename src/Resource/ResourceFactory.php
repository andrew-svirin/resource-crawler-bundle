<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Node\NodeInterface;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathRegexCreator;

/**
 * Factory for resource.
 *
 * @interal
 */
final class ResourceFactory
{
    public function __construct(
        private readonly PathRegexCreator $pathRegexCreator
    ) {
    }

    /**
     * Create Web resource.
     */
    public function createWeb(NodeInterface $node, ?array $pathMasks = null): WebResource
    {
        $pathRegex = $this->pathRegexCreator->create($pathMasks);

        return new WebResource($node, $pathRegex);
    }

    /**
     * Create Disk resource.
     */
    public function createDisk(NodeInterface $node, ?array $pathMasks = null): DiskResource
    {
        $pathRegex = $this->pathRegexCreator->create($pathMasks);

        return new DiskResource($node, $pathRegex);
    }
}
