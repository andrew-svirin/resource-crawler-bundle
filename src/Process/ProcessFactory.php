<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Resource;

/**
 * Factory for the process.
 *
 * @interal
 */
final class ProcessFactory
{
    public function create(Resource $resource): CrawlingProcess
    {
        $processId = $this->resolveProcessId($resource);

        return new CrawlingProcess($processId, $resource);
    }

    private function resolveProcessId(Resource $resource): string
    {
        return preg_replace('/[^[:alnum:]]/', '_', $resource->getRoot()->getUri()->getPath());
    }
}
