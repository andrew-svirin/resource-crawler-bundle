<?php

namespace AndrewSvirin\ResourceCrawlerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Checks to see if the filesystem client exists.
 *
 * @internal
 * @final
 */
class CheckFilesystemClientPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        // TODO.
    }
}
