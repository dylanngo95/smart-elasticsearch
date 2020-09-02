<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\Indexer\Action;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Smart\UrlRewriteIndex\ElasticSearch\Client\ElasticSearch;
use Smart\UrlRewriteIndex\Helper\Data;
use Smart\UrlRewriteIndex\Logger\Logger;
use Smart\UrlRewriteIndex\Model\UrlRewriteFactory;

/**
 * Class Full
 * @package Smart\UrlRewriteIndex\Indexer\Action
 */
class Full
{
    /**
     * Url rewrite index table name
     */
    const MAIN_INDEX_TABLE = 'url_rewrite_index';

    /**
     * Url rewrite index table tmp name
     */
    const MAIN_INDEX_TABLE_TMP = 'url_rewrite_index_tmp';

    /**
     * Primary key
     */
    const KEY = 'url_rewrite_id';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var ElasticSearch
     */
    private $elasticSearch;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Data
     */
    private $config;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * Full constructor.
     * @param ResourceConnection $resourceConnection
     * @param AdapterInterface $connection
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param ElasticSearch $elasticSearch
     * @param Logger $logger
     * @param Data $config
     * @param Batch $batch
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AdapterInterface $connection,
        UrlRewriteFactory $urlRewriteFactory,
        ElasticSearch $elasticSearch,
        Logger $logger,
        Data $config,
        Batch $batch
    ) {
        $this->resource = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->elasticSearch = $elasticSearch;
        $this->logger = $logger;
        $this->config = $config;
        $this->batch = $batch;
    }

    /**
     * Reindex all
     */
    public function indexAll()
    {
        if ($this->config->isEnableElasticSearch()) {
            $this->indexElasticSearch();
        } else {
            $this->indexMySQL();
        }
    }

    /**
     * indexElasticSearch
     */
    private function indexElasticSearch()
    {
        $urlRewrites = $this->rebuildUrlRewriteIndex();
        if ($urlRewrites) {
            foreach ($this->batch->getItems($urlRewrites, $this->config->getBatchSize()) as $urlRewrite) {
                $this->elasticSearch->index($urlRewrite[0]);
            }
        }
    }

    /**
     * @return \Generator
     */
    private function rebuildUrlRewriteIndex()
    {
        yield $this->getUrlRewriteData();
    }

    /**
     * indexMySQL
     */
    private function indexMySQL()
    {
        $isCreatedTmp = $this->indexTmp();
        if ($isCreatedTmp) {
            $this->connection->dropTable(self::MAIN_INDEX_TABLE . '_replica');
            $this->connection->renameTable(self::MAIN_INDEX_TABLE, self::MAIN_INDEX_TABLE . '_replica');
            $this->connection->renameTable(self::MAIN_INDEX_TABLE_TMP, self::MAIN_INDEX_TABLE);
            $this->connection->renameTable(self::MAIN_INDEX_TABLE . '_replica', self::MAIN_INDEX_TABLE . '_tmp');
            $this->connection->truncateTable(self::MAIN_INDEX_TABLE_TMP);
        }
    }

    /**
     * @return bool
     */
    public function indexTmp()
    {
        $isCreatedTmp = false;
        $urlRewrites = $this->getUrlRewriteData();
        $data = [];
        foreach ($urlRewrites as $urlRewrite) {
            $urlRewrite['url_rewrite_id'] = null;
            $data[] = $urlRewrite;
        }

        try {
            $this->connection->insertMultiple(
                $this->getTable(self::MAIN_INDEX_TABLE_TMP),
                $data
            );
            $isCreatedTmp = true;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $isCreatedTmp;
    }

    /**
     * @return array
     */
    public function getUrlRewriteData()
    {
        $select = $this->connection->select()
            ->from(
                ['m' => $this->getTable('url_rewrite')]
            );
        return $this->connection->fetchAll(
            $select,
            null
        );
    }

    /**
     * @param $table
     * @return string
     */
    public function getTable($table)
    {
        return $this->resource->getTableName($table);
    }
}
