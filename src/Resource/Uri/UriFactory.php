<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Uri;

/**
 * Factory for URI.
 *
 * @interal
 */
final class UriFactory
{
    /**
     * Create HTTP URI.
     */
    public function createHttp(string $path): HttpUri
    {
        return new HttpUri($path);
    }

    /**
     * Create FS URI.
     */
    public function createFs(string $path): FsUri
    {
        return new FsUri($path);
    }
}
