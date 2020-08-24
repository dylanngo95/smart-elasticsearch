<?php

declare(strict_types = 1);

namespace Smart\ElasticSearch\Indexer;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Smart\ElasticSearch\Indexer\Action\Full;
use Smart\ElasticSearch\Indexer\Action\Rows;


/**
 * Class UrlRewriteIndexer
 * @package Smart\ElasticSearch\Indexer
 */
class UrlRewriteIndexer implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    /**
     * @var Full
     */
    private $indexFull;

    /**
     * @var Rows
     */
    private $indexRows;

    public function __construct(
        Full $indexFull
    )
    {
        $this->indexFull = $indexFull;
    }

    public function executeFull()
    {
        $this->indexFull->getTable('url_rewrite');
        $a = 1;
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        // TODO: Implement executeList() method.
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        // TODO: Implement executeRow() method.
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
        // TODO: Implement execute() method.
    }
}
