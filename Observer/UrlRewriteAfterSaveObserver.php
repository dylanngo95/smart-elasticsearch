<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class UrlRewriteAfterSaveObserver
 * @package Smart\ElasticSearch\Observer
 */
class UrlRewriteAfterSaveObserver implements ObserverInterface
{
    /**
     * @var ResourceConnection;
     */
    protected $resourceConnection;
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * Url rewrite index table name
     */
    const MAIN_INDEX_TABLE = 'url_rewrite_index';

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $urls = $observer->getEvent()->getUrl();
        $data = $this->prepareUrls($urls);
        try {
            $this->connection->insertMultiple(
                $this->getTable(self::MAIN_INDEX_TABLE),
                $data
            );
        } catch (\Exception $e) {
            $tmp = $e;
        }
    }

    /**
     * @param $urls
     * @return array
     */
    public function prepareUrls($urls)
    {
        $data = [];
        foreach ($urls as $url) {
            if ($url['redirect_type'] == 0) {
                $data[] = $url;
            }
        }
        return $data;
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
