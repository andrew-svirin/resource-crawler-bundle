<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource;

/**
 * Factory for resource.
 *
 * @interal
 */
final class ResourceFactory
{
    /**
     * Create HTTP resource.
     */
    public function createHttp(NodeInterface $node): HttpResource
    {
        return new HttpResource($node);
    }

    /**
     * Create Filesystem resource.
     */
    public function createFs(NodeInterface $node): FsResource
    {
        return new FsResource($node);
    }
}
