<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class UrlRewrite
 * @package Smart\ElasticSearch\Model\ResourceModel
 */
class UrlRewrite extends AbstractDb
{
    const TABLE_NAME = 'url_rewrite_index';
    const PRIMARY_KEY = 'url_rewrite_id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::PRIMARY_KEY);
    }
}
