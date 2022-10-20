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
    public function createHttp(string $url): HttpResource
    {
        return new HttpResource($url);
    }
}
