<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = \Monolog\Logger::WARNING;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/url-rewrite-index.log';
}
