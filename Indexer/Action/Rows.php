<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Indexer\Action;


use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Smart\ElasticSearch\Logger\Logger;
use Smart\ElasticSearch\Model\UrlRewrite;
use Smart\ElasticSearch\Model\UrlRewriteFactory;

/**
 * Class Rows
 * @package Smart\ElasticSearch\Indexer\Action
 */
class Rows
{
    /**
     * Url rewrite index table name
     */
    const MAIN_INDEX_TABLE = 'url_rewrite_index';
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
     * @var Logger
     */
    private $logger;

    /**
     * Rows constructor.
     * @param ResourceConnection $resourceConnection
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param Logger $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        UrlRewriteFactory $urlRewriteFactory,
        Logger $logger
    )
    {
        $this->resource = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $entityIds
     */
    public function index($entityIds)
    {
        foreach ($entityIds as $entityId) {
            $urlRewriteSelect = $this->getUrlRewriteData($entityId);
            if ($urlRewriteSelect) {
                /** @var UrlRewrite $urlRewrite */
                $urlRewrite = $this->urlRewriteFactory->create();
                foreach ($urlRewriteSelect as $key => $value) {
                    if ($key != self::KEY){
                        $urlRewrite->setData($key, $value);
                    }
                }
                try {
                    $urlRewrite->save();
                } catch (\Exception $e) {
                    $this->logger->addWarning($e->getMessage());
                }
            }
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
     * @return array
     */
    public function getUrlRewriteData($entityId)
    {
        $select = $this->connection->select()
            ->from(
                ['m' => $this->getTable('url_rewrite')]
            )->where(
                'm.url_rewrite_id = ?',
                $entityId
            );

        return $this->connection->fetchRow(
            $select,
            null
        );
    }
}
