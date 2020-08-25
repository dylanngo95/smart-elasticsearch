<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class UrlRewrite
 * @package Smart\ElasticSearch\Model
 */
class UrlRewrite extends AbstractModel
{
    /**
     * event prefix for dispatch
     * @var string
     */
    protected $_eventPrefix = 'url_rewrite_index';

    protected function _construct()
    {
        $this->_init(ResourceModel\UrlRewrite::class);
    }
}
