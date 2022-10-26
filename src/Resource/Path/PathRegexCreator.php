<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path;

use LogicException;
use RuntimeException;

/**
 * Creator for path regex.
 *
 * @interal
 */
final class PathRegexCreator
{
    public function create(array $pathMasks): PathRegex
    {
        $pathRegex = new PathRegex();

        foreach ($pathMasks as $pathMask) {
            $op = $this->resolveOp($pathMask);
            $ex = $this->resolveEx($pathMask);

            if ('+' === $op) {
                $pathRegex->addAllowed($ex);
            } elseif ('-' === $op) {
                $pathRegex->addDisallowed($ex);
            } else {
                throw new LogicException('First symbol incorrect.');
            }
        }

        $expression = $this->resolveExpression(
            $pathRegex->getAllowedExpressions(),
            $pathRegex->getDisallowedExpressions()
        );
        $pathRegex->setExpression($expression);

        return $pathRegex;
    }

    private function resolveOp(string $pathMask): string
    {
        $op = substr($pathMask, 0, 1);

        if (!in_array($op, ['+', '-'])) {
            throw new RuntimeException('First symbol incorrect. Allowed: "+", "-"');
        }

        return $op;
    }

    private function resolveEx(string $pathMask): string
    {
        $ex = substr($pathMask, 1);

        return str_replace(['.', '/', '*'], ['\.', '\/', '.*'], $ex);
    }

    /**
     * Convert from `+site -sitt +sitte -erd +kitte -emb.ed`
     *         to `/(sitt|emb\.ed|embb)|(site|sitte|kitte)/`
     */
    private function resolveExpression(array $allowedExs, array $disallowedExs): string
    {
        $disallowed = implode('|', $disallowedExs);
        if (empty($disallowed)) {
            $disallowed = '$^';
        }

        $allowed = implode('|', $allowedExs);
        if (empty($allowed)) {
            $allowed = '*';
        }

        return sprintf('/(%s)|(%s)/', $disallowed, $allowed);
    }
}
