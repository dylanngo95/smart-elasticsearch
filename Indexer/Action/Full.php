<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Indexer\Action;


use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Class Full
 * @package Smart\ElasticSearch\Indexer\Action
 */
class Full
{
    /**
     * Url rewrite index table name
     */
    const MAIN_INDEX_TABLE = 'url_rewrite_index';
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var AdapterInterface
     */
    protected $connection;

    public function __construct(
        ResourceConnection $resourceConnection,
        AdapterInterface $connection
    )
    {
        $this->resource = $connection;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @param array $entityIds
     */
    public function index($entityIds)
    {
        foreach ($entityIds as $entityId) {
            $tmp = $this->getUrlRewriteData($entityId);
            $a = 0;
        }
    }

    /**
     * @param $table
     * @return string
     */
    public function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->getTable(self::MAIN_INDEX_TABLE);
    }

    /**
     * @param $entityId
     * @return Select
     */
    public function getUrlRewriteSelect($entityId)
    {
        return $this->connection->select()
            ->from(
                ['m' => $this->getTable('url_rewrite')],
                []
            )->joinLeft(
                ['p' => $this->getTable('catalog_url_rewrite_product_category')],
                'm.url_rewrite_id = p.url_rewrite_id',
                []
            )->where(
                'm.url_rewrite_id = ?',
                $entityId
            );
    }

    /**
     * @param $entityId
     * @return \Zend_Db_Statement_Interface
     */
    public function getUrlRewriteData($entityId)
    {
        return $this->connection->query(
            $this->getUrlRewriteSelect($entityId),
            null
        );
    }
}
