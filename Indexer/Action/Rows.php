<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Indexer\Action;


use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\ProductCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Rows
 * @package Smart\ElasticSearch\Indexer\Action
 */
class Rows
{
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ProductCollection
     */
    protected $categoryProductCollection;

    /**
     * @var Product
     */
    protected $categoryProductResourceModel;

    /**
     * Url rewrite index table name
     */
    const MAIN_INDEX_TABLE = 'url_rewrite_index';

    /**
     * Url rewrite index temp table name
     */
    const TEMPORARY_TABLE_SUFFIX = '_tmp';

    /**
     * Rows constructor.
     * @param ResourceConnection $resource
     * @param AdapterInterface $connection
     * @param ProductCollection $categoryProductCollection
     * @param Product $product
     */
    public function __construct(
        ResourceConnection $resource,
        AdapterInterface $connection,
        ProductCollection $categoryProductCollection,
        Product $product
    )
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->categoryProductCollection = $categoryProductCollection;
        $this->categoryProductResourceModel = $product;
    }

    /**
     * Pass url_rewrite_id
     *
     * @param array $entityIds
     */
    public function index(array $entityIds = [15, 16, 17])
    {
        foreach ($entityIds as $entityId){
            $tmp = $this->getUrlRewriteData($entityId);
        }
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

    /**
     * @param $entityId
     * @return \Magento\Framework\DB\Select
     */
    public function getUrlRewriteSelect($entityId)
    {
        $select = $this->connection->select()
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
        return $select;
    }

    /**
     * @return string
     */
    public function getMainTable()
    {
        return $this->getTable(self::MAIN_INDEX_TABLE);
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
