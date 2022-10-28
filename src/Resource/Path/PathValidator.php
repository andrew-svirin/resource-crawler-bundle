<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\FsUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\HttpUri;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriInterface;
use LogicException;

/**
 * Validator for path.
 *
 * @interal
 */
final class PathValidator
{
    public function isValid(UriInterface $parentUri, string $childPath): bool
    {
        if ($parentUri instanceof HttpUri) {
            $isValid = $this->isValidPathHttp($parentUri, $childPath);
        } elseif ($parentUri instanceof FsUri) {
            $isValid = $this->isValidPathFs($parentUri, $childPath);
        } else {
            throw new LogicException('Incorrect uri.');
        }

        return $isValid;
    }

    private function isValidPathHttp(HttpUri $parentUri, string $childPath): bool
    {
        return true;
    }

    private function isValidPathFs(FsUri $parentUri, string $childPath): bool
    {
        return true;
    }
}
