<?php

namespace AndrewSvirin\ResourceCrawlerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Checks to see if the web client exists.
 *
 * @internal
 * @final
 */
class CheckWebClientPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        // TODO.
    }
}
