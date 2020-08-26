<?php

declare(strict_types = 1);

namespace Smart\ElasticSearch\Indexer;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Smart\ElasticSearch\Indexer\Action\Full;
use Smart\ElasticSearch\Indexer\Action\Rows;
use Smart\ElasticSearch\Logger\Logger;


/**
 * Class UrlRewriteIndexer
 * @package Smart\ElasticSearch\Indexer
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

    public function __construct(
        Rows $indexRows,
        Full $indexFull,
        Logger $logger,
        IndexerRegistry $indexerRegistry
    )
    {
        $this->indexRows = $indexRows;
        $this->indexFull = $indexFull;
        $this->logger = $logger;
        $this->indexerRegistry = $indexerRegistry;
    }

    public function executeFull()
    {
        $this->logger->info('executeFull');
        $this->indexRows->index(['15', '16', '17', '18']);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->logger->info('executeList');
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
        $this->logger->info('executeRow');
        $this->indexRows->index([$id]);;
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
        $this->logger->info('execute');
        $this->logger->info(print_r($ids, true));

        $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
        if ($indexer->isInvalid()) {
            return;
        }

        $this->logger->info('execute finish');

        $this->indexRows->index($ids);
    }
}
