<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process;

use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;

/**
 * Interface for process store.
 */
interface ProcessStoreInterface
{
  /**
   * Push task to for_processing stack.
   * Put a task to for_processing stack.
   */
  public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): void;

  /**
   * Pop task from `for_processing` stack.
   * Take a task form `for_processing` stack and move task to `in_process` stack.
   */
  public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask;

  /**
   * Pop task from `in_process` stack.
   * Take a task form `in_process` stack.
   */
  public function popInProcessTask(CrawlingProcess $process): ?CrawlingTask;

  /**
   * Push task to `processed` stack.
   * Move task from `in_process` stack to `processed` stack.
   */
  public function pushProcessedTask(CrawlingProcess $process, CrawlingTask $task): void;

  /**
   * Push task to `ignored` stack.
   * Move task from `in_process` stack to `ignored` stack.
   */
  public function pushIgnoredTask(CrawlingProcess $process, CrawlingTask $task): void;

  /**
   * Push task to `errored` stack.
   * Move task from `in_process` stack to `errored` stack.
   */
  public function pushErroredTask(CrawlingProcess $process, CrawlingTask $task): void;

  /**
   * Does task already exists.
   * Check if stack exists in any of stack.
   */
  public function taskExists(CrawlingProcess $process, CrawlingTask $task): bool;
  /**
   * Delete process.
   * Delete all stacks of tasks.
   */
  public function deleteProcess(CrawlingProcess $process): void;
}
