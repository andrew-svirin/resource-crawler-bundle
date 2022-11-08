<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use RuntimeException;
use Symfony\Component\Lock\LockFactory;

/**
 * Abstract Process store.
 */
abstract class ProcessStore implements ProcessStoreInterface
{
  public function __construct(
    private readonly bool $isLockable,
    private readonly ?LockFactory $lockFactory = null
  ) {
  }

  protected function operateStore(callable $closure): ?string
  {
    if ($this->isLockable) {
      return $this->operateStoreWithLocking($closure);
    } else {
      return call_user_func($closure);
    }
  }

  private function operateStoreWithLocking(callable $closure): ?string
  {
    $lock = $this->lockFactory->createLock('crawling-process', 30);

    if (!$lock->acquireRead()) {
      throw new RuntimeException('Can not lock file.');
    }
    try {
      return call_user_func($closure);
    } finally {
      $lock->release();
    }
  }
}
