<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Process\Store\Db;

use AndrewSvirin\ResourceCrawlerBundle\Process\CrawlingProcess;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\OperateStoreClosure;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\ProcessStore;
use AndrewSvirin\ResourceCrawlerBundle\Process\Store\ProcessStoreInterface;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\CrawlingTask;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskFactory;
use AndrewSvirin\ResourceCrawlerBundle\Process\Task\TaskPacker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;

/**
 * Store in the database.
 */
final class DbProcessStore extends ProcessStore implements ProcessStoreInterface
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    readonly bool $isLockable,
    private readonly TaskPacker $taskPacker,
    private readonly TaskFactory $taskFactory,
    readonly ?LockFactory $lockFactory = null
  ) {
    parent::__construct($isLockable, $lockFactory);
  }

  public function pushForProcessingTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    $task->setStatus(CrawlingTask::STATUS_FOR_PROCESSING);

    $conn = $this->entityManager->getConnection();

    $sqlIns  = '
      INSERT INTO `resource_crawler_nodes` (`process_id`, `status`, `type`, `uri_type`, `uri_path`, `code`)
      VALUES (
        (SELECT id FROM resource_crawler_processes p WHERE p.name = :process_name),
        :status,
        :type,
        :uri_type,
        :uri_path,
        :code
      )';
    $stmtIns = $conn->prepare($sqlIns);
    $stmtIns->bindValue('process_name', $task->getProcess()->getName());
    $stmtIns->bindValue('status', $task->getStatus());
    $stmtIns->bindValue('type', $this->taskPacker->packNodeType($task->getNode()));
    $stmtIns->bindValue('uri_type', $this->taskPacker->packUriType($task->getNode()->getUri()));
    $stmtIns->bindValue('uri_path', $task->getNode()->getUri()->getPath());
    $stmtIns->bindValue('code', $task->getNode()->getResponse()?->getCode());

    $sqlExists  = '
      SELECT *
      FROM `resource_crawler_nodes` n
      WHERE n.uri_path = :uri_path
      LIMIT 1';
    $stmtExists = $conn->prepare($sqlExists);
    $stmtExists->bindValue('uri_path', $task->getNode()->getUri()->getPath());

    $op = new OperateStoreClosure($this, function () use ($stmtExists, $stmtIns): bool {
      if ($stmtExists->executeQuery()->fetchOne()) {
        return false;
      }

      return (bool) $stmtIns->executeStatement();
    });

    return $this->operateStore($op);
  }

  public function popForProcessingTask(CrawlingProcess $process): ?CrawlingTask
  {
    $pkdTask = null;

    $conn = $this->entityManager->getConnection();

    $sqlSel  = '
      SELECT n.*
      FROM `resource_crawler_nodes` n
      INNER JOIN `resource_crawler_processes` p ON n.process_id = p.id
      WHERE n.status = :status AND p.name = :process_name
      LIMIT 1';
    $stmtSel = $conn->prepare($sqlSel);
    $stmtSel->bindValue('status', CrawlingTask::STATUS_FOR_PROCESSING);
    $stmtSel->bindValue('process_name', $process->getName());

    $sqlUpd  = '
      UPDATE `resource_crawler_nodes` n
      SET n.`status` = :status
      WHERE n.id = :id';
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->bindValue('status', CrawlingTask::STATUS_IN_PROCESS);

    $op = new OperateStoreClosure($this, function () use ($stmtSel, $stmtUpd, &$pkdTask): bool {
      $pkdTask = $stmtSel->executeQuery()->fetchAssociative();

      if (false === $pkdTask) {
        return false;
      }

      $stmtUpd->bindValue('id', $pkdTask['id']);

      return (bool) $stmtUpd->executeStatement();
    });

    $operate = $this->operateStore($op);

    if (!$operate) {
      return null;
    }

    if (null === $pkdTask) {
      return null;
    }

    /** @var array{uri_path: string, uri_type: string, type: string}|null $pkdTask */

    $uri  = $this->taskPacker->unpackUri($pkdTask['uri_type'], $pkdTask['uri_path']);
    $node = $this->taskPacker->unpackNode($pkdTask['type'], $uri);

    $task = $this->taskFactory->create($process, $node);

    $task->setStatus(CrawlingTask::STATUS_IN_PROCESS);

    return $task;
  }

  public function pushProcessedTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->pushTask($process, $task, CrawlingTask::STATUS_PROCESSED);
  }

  public function pushIgnoredTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->pushTask($process, $task, CrawlingTask::STATUS_IGNORED);
  }

  public function pushErroredTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    return $this->pushTask($process, $task, CrawlingTask::STATUS_ERRORED);
  }

  public function revertTask(CrawlingProcess $process, CrawlingTask $task): bool
  {
    $conn = $this->entityManager->getConnection();

    /** @var \Doctrine\DBAL\Statement[] $stmtDelList */
    $stmtDelList = [];

    $sqlDel = '
      DELETE n
      FROM `resource_crawler_nodes` n
      INNER JOIN `resource_crawler_processes` p ON n.process_id = p.id
      WHERE n.uri_path = :uri_path AND p.name = :process_name;';
    foreach ($task->getPushedForProcessingPaths() as $path) {
      $stmtDelList[] = $stmtDel = $conn->prepare($sqlDel);
      $stmtDel->bindValue('uri_path', $path);
      $stmtDel->bindValue('process_name', $process->getName());
    }

    $sqlUpd  = '
      UPDATE `resource_crawler_nodes` n
      INNER JOIN `resource_crawler_processes` p ON n.process_id = p.id
      SET n.`status` = :new_status,
          n.`code` = :code
      WHERE n.uri_path = :uri_path AND n.status = :old_status AND p.name = :process_name';
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->bindValue('uri_path', $task->getNode()->getUri()->getPath());
    $stmtUpd->bindValue('old_status', $task->getStatus());
    $stmtUpd->bindValue('new_status', CrawlingTask::STATUS_FOR_PROCESSING);
    $stmtUpd->bindValue('process_name', $process->getName());
    $stmtUpd->bindValue('code', null);

    $op = new OperateStoreClosure($this, function () use ($stmtDelList, $stmtUpd): bool {
      foreach ($stmtDelList as $stmtDel) {
        if (!$stmtDel->executeStatement()) {
          return false;
        }
      }

      return (bool) $stmtUpd->executeStatement();
    });

    $operate = $this->operateStore($op);

    if (!$operate) {
      return false;
    }

    $task->setStatus(CrawlingTask::STATUS_FOR_PROCESSING);

    return true;
  }

  private function pushTask(CrawlingProcess $process, CrawlingTask $task, string $status): bool
  {
    $conn = $this->entityManager->getConnection();

    $sqlUpd  = '
      UPDATE `resource_crawler_nodes` n
      INNER JOIN `resource_crawler_processes` p ON n.process_id = p.id
      SET n.`status` = :new_status,
          n.`code` = :code
      WHERE n.uri_path = :uri_path AND n.status = :old_status AND p.name = :process_name';
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->bindValue('uri_path', $task->getNode()->getUri()->getPath());
    $stmtUpd->bindValue('old_status', $task->getStatus());
    $stmtUpd->bindValue('new_status', $status);
    $stmtUpd->bindValue('process_name', $process->getName());
    $stmtUpd->bindValue('code', $task->getNode()->getResponse()?->getCode());

    $op = new OperateStoreClosure($this, function () use ($stmtUpd): bool {
      return (bool) $stmtUpd->executeStatement();
    });

    $operate = $this->operateStore($op);

    $task->setStatus($status);

    return $operate;
  }

  public function createProcess(CrawlingProcess $process): bool
  {
    $conn = $this->entityManager->getConnection();

    $sqlIns  = '
      INSERT IGNORE INTO `resource_crawler_processes` (`name`)
      VALUES (
        :name
      )';
    $stmtIns = $conn->prepare($sqlIns);
    $stmtIns->bindValue('name', $process->getName());

    $sqlExists  = '
      SELECT *
      FROM `resource_crawler_processes` n
      WHERE n.name = :name
      LIMIT 1';
    $stmtExists = $conn->prepare($sqlExists);
    $stmtExists->bindValue('name', $process->getName());

    $op = new OperateStoreClosure($this, function () use ($stmtExists, $stmtIns): bool {
      if (!$stmtExists->executeQuery()->fetchOne()) {
        $stmtIns->executeStatement();
      }

      return true;
    });

    return $this->operateStore($op);
  }

  public function deleteProcess(CrawlingProcess $process): bool
  {
    $conn = $this->entityManager->getConnection();

    $sqlDel = '
      DELETE n
      FROM `resource_crawler_nodes` n
      INNER JOIN `resource_crawler_processes` p ON n.process_id = p.id
      WHERE p.name = :name;
      DELETE p
      FROM `resource_crawler_processes` p
      WHERE p.name = :name;';

    $stmtDel = $conn->prepare($sqlDel);
    $stmtDel->bindValue('name', $process->getName());

    $op = new OperateStoreClosure($this, function () use ($stmtDel): bool {
      $stmtDel->executeStatement();

      return true;
    });

    return $this->operateStore($op);
  }

  public function countTasks(CrawlingProcess $process): array
  {
    $conn = $this->entityManager->getConnection();

    $sqlSel    = '
      SELECT `status`,COUNT(*)
      FROM resource_crawler_nodes
      GROUP BY `status`;';
    $stmtSel   = $conn->prepare($sqlSel);
    $keyValues = $stmtSel->executeQuery()->fetchAllKeyValue();

    $counts = [];

    foreach (CrawlingTask::ALL_STATUSES as $status) {
      $counts[$status] = $keyValues[$status] ?? 0;
    }

    return $counts;
  }
}
