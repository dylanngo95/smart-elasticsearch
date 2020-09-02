<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Smart\UrlRewriteIndex\ElasticSearch\Client\ElasticSearch;
use Smart\UrlRewriteIndex\Helper\Data;
use Smart\UrlRewriteIndex\Logger\Logger;

/**
 * Class UrlRewriteAfterSaveObserver
 * @package Smart\UrlRewriteIndex\Observer
 */
class UrlRewriteAfterSaveObserver implements ObserverInterface
{
    /**
     * Url rewrite index table name
     */
    const MAIN_INDEX_TABLE = 'url_rewrite_index';

    /**
     * Indexer Id
     */
    const INDEXER_ID = 'url_rewrite';

    /**
     * @var ResourceConnection;
     */
    protected $resourceConnection;
    /**
     * @var AdapterInterface
     */
    protected $connection;
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

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
     * UrlRewriteAfterSaveObserver constructor.
     * @param ResourceConnection $resourceConnection
     * @param IndexerRegistry $indexerRegistry
     * @param ElasticSearch $elasticSearch
     * @param Data $config
     * @param Logger $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        IndexerRegistry $indexerRegistry,
        ElasticSearch $elasticSearch,
        Data $config,
        Logger $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->indexerRegistry = $indexerRegistry;
        $this->elasticSearch = $elasticSearch;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
        if ($indexer->isScheduled()) {
            return;
        }

        $urls = $observer->getEvent()->getUrl();
        $this->deleteOldUrls($urls);

        $data = [];
        foreach ($urls as $url){
            $data[] = $url->toArray();
        }

        if ($this->config->isEnableElasticSearch()) {
            $this->indexWithElasticSearch($data);
        } else {
            $this->indexWithMySQL($data);
        }

    }

    /**
     * @param array $data
     */
    private function indexWithElasticSearch($data)
    {
        $this->elasticSearch->index($data);
    }

    /**
     * @param array $data
     */
    private function indexWithMySQL($data)
    {
        try {
            $this->connection->insertMultiple(
                $this->getTable(self::MAIN_INDEX_TABLE),
                $data
            );
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Delete old URLs from DB.
     *
     * @param  UrlRewrite[] $urls
     * @return void
     */
    private function deleteOldUrls(array $urls): void
    {
        $oldUrlsSelect = $this->connection->select();
        $oldUrlsSelect->from(
            $this->resourceConnection->getTableName(self::MAIN_INDEX_TABLE)
        );

        $uniqueEntities = $this->prepareUniqueEntities($urls);
        foreach ($uniqueEntities as $storeId => $entityTypes) {
            foreach ($entityTypes as $entityType => $entities) {
                $oldUrlsSelect->orWhere(
                    $this->connection->quoteIdentifier(
                        UrlRewrite::STORE_ID
                    ) . ' = ' . $this->connection->quote($storeId, 'INTEGER') .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_ID
                    ) . ' IN (' . $this->connection->quote($entities, 'INTEGER') . ')' .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_TYPE
                    ) . ' = ' . $this->connection->quote($entityType)
                );
            }
        }

        // prevent query locking in a case when nothing to delete
        $checkOldUrlsSelect = clone $oldUrlsSelect;
        $checkOldUrlsSelect->reset(Select::COLUMNS);
        $checkOldUrlsSelect->columns('count(*)');
        $hasOldUrls = (bool)$this->connection->fetchOne($checkOldUrlsSelect);

        if ($hasOldUrls) {
            $this->connection->query(
                $oldUrlsSelect->deleteFromSelect(
                    $this->resourceConnection->getTableName(self::MAIN_INDEX_TABLE)
                )
            );
        }
    }

    /**
     * Prepare array with unique entities
     *
     * @param  UrlRewrite[] $urls
     * @return array
     */
    private function prepareUniqueEntities(array $urls): array
    {
        $uniqueEntities = [];
        /** @var UrlRewrite $url */
        foreach ($urls as $url) {
            $entityIds = (!empty($uniqueEntities[$url->getStoreId()][$url->getEntityType()])) ?
                $uniqueEntities[$url->getStoreId()][$url->getEntityType()] : [];

            if (!\in_array($url->getEntityId(), $entityIds)) {
                $entityIds[] = $url->getEntityId();
            }
            $uniqueEntities[$url->getStoreId()][$url->getEntityType()] = $entityIds;
        }

        return $uniqueEntities;
    }

    /**
     * @param $table
     * @return string
     */
    public function getTable($table)
    {
        return $this->resourceConnection->getTableName($table);
    }
}
