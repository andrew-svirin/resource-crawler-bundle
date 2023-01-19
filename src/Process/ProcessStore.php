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

  protected function operateStore(OperateStoreClosure $closure): ?bool
  {
    if ($this->isLockable) {
      return $this->operateStoreWithLocking($closure);
    } else {
      return $closure->call();
    }
  }

  private function operateStoreWithLocking(OperateStoreClosure $closure): bool
  {
    $lock = $this->lockFactory->createLock('crawling-process', 30);

    if (!$lock->acquire(true)) {
      throw new RuntimeException('Can not lock file.');
    }
    try {
      return $closure->call();
    } finally {
      $lock->release();
    }
  }
}
