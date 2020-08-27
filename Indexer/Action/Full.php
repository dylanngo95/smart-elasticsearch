<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\Indexer\Action;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
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
     * @var Logger
     */
    private $logger;

    public function __construct(
        ResourceConnection $resourceConnection,
        AdapterInterface $connection,
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
     * Reindex all
     */
    public function indexAll()
    {
        $isCreatedTmp = $this->indexTmp();
        if ($isCreatedTmp) {
            $this->connection->dropTable(self::MAIN_INDEX_TABLE.'_replica');
            $this->connection->renameTable(self::MAIN_INDEX_TABLE, self::MAIN_INDEX_TABLE.'_replica');
            $this->connection->renameTable(self::MAIN_INDEX_TABLE_TMP, self::MAIN_INDEX_TABLE);
            $this->connection->renameTable(self::MAIN_INDEX_TABLE.'_replica', self::MAIN_INDEX_TABLE.'_tmp');
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
        foreach ($urlRewrites as $urlRewrite)
        {
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
