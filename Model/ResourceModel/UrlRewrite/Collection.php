<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\Model\ResourceModel\UrlRewrite;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Smart\UrlRewriteIndex\Model\ResourceModel\UrlRewrite
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'url_rewrite_id';

    protected $_eventPrefix = 'smart_elastic_search_url_rewrite';

    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Smart\UrlRewriteIndex\Model\UrlRewrite::class,
            \Smart\UrlRewriteIndex\Model\ResourceModel\UrlRewrite::class
        );
    }
}
