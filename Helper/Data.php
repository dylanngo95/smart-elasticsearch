<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\Helper;


/**
 * Class Data
 * @package Smart\UrlRewriteIndex\Helper
 */
class Data
{

    /**
     * @return bool
     */
    public function isEnableElasticSearch()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getIndexName()
    {
        return "url_rewrite_index";
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return 'localhost';
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return '9200';
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return 500;
    }
}
