<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\Helper;


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

}
