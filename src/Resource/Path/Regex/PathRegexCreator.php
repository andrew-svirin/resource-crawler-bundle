<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex;

use RuntimeException;

/**
 * Creator for path regex.
 *
 * @interal
 */
final class PathRegexCreator
{
  /**
   * @param string[] $pathMasks Mask for path.
   *                            `+<rule>` - to allow, `-<rule>` - to disallow.
   *                            `+site.com/page` - allowing mask
   *                            `-embed` - disallowing mask
   */
  public function create(array $pathMasks): PathRegex
  {
    $pathRegex = new PathRegex();

    foreach ($pathMasks as $pathMask) {
      $pathMaskOp   = $this->resolvePathMaskOperation($pathMask);
      $pathMaskExpr = $this->resolvePathMaskExpression($pathMask);

      $this->addPathMaskOperation($pathMaskOp, $pathMaskExpr, $pathRegex);
    }

    $expression = $this->resolveExpression(
      $pathRegex->getAllowedExpressions(),
      $pathRegex->getDisallowedExpressions()
    );
    $pathRegex->setExpression($expression);

    return $pathRegex;
  }

  private function addPathMaskOperation(string $pathMaskOp, string $pathMaskExpr, PathRegex $pathRegex): void
  {
    if ('+' === $pathMaskOp) {
      $pathRegex->addAllowed($pathMaskExpr);
    } elseif ('-' === $pathMaskOp) {
      $pathRegex->addDisallowed($pathMaskExpr);
    } else {
      throw new RuntimeException('Path mask first symbol invalid. Allowed: "+", "-"');
    }
  }

  private function resolvePathMaskOperation(string $pathMask): string
  {
    $operation = substr($pathMask, 0, 1);

    if (!in_array($operation, ['+', '-'])) {
      throw new RuntimeException('Path mask first symbol invalid. Allowed: "+", "-"');
    }

    return $operation;
  }

  private function resolvePathMaskExpression(string $pathMask): string
  {
    $expression = substr($pathMask, 1);

    return str_replace(['.', '/', '*'], ['\.', '\/', '.*'], $expression);
  }

  /**
   * Convert from `+site -sitt +sitte -erd +kitte -emb.ed`
   *         to `/(sitt|emb\.ed|embb)|(site|sitte|kitte)/`
   * @param string[] $allowedExs
   * @param string[] $disallowedExs
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
