<?php

declare(strict_types = 1);

namespace Smart\UrlRewriteIndex\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;
use Smart\UrlRewriteIndex\Indexer\Action\Full;
use Smart\UrlRewriteIndex\Indexer\Action\Rows;
use Smart\UrlRewriteIndex\Logger\Logger;

/**
 * Class UrlRewriteIndexer
 * @package Smart\UrlRewriteIndex\Indexer
 */
class UrlRewriteIndexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer Id
     */
    const INDEXER_ID = 'url_rewrite';

    /**
     * @var Full
     */
    private $indexFull;
    /**
     * @var Rows
     */
    private $indexRows;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * UrlRewriteIndexer constructor.
     * @param Rows $indexRows
     * @param Full $indexFull
     * @param Logger $logger
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Rows $indexRows,
        Full $indexFull,
        Logger $logger,
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexRows = $indexRows;
        $this->indexFull = $indexFull;
        $this->logger = $logger;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * executeFull
     */
    public function executeFull()
    {
        $this->indexFull->indexAll();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->indexRows->index($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->indexRows->index([$id]);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
        if ($indexer->isInvalid()) {
            return;
        }
//        $this->indexRows->index($ids);
    }
}
