<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\ElasticSearch\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Smart\UrlRewriteIndex\Helper\Data;
use Smart\UrlRewriteIndex\Logger\Logger;

/**
 * Class ElasticSearch
 * @package Smart\UrlRewriteIndex\ElasticSearch\Client
 */
class ElasticSearch
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Data
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ElasticSearch constructor.
     * @param ClientBuilder $clientBuilder
     * @param Data $config
     * @param Logger $logger
     */
    public function __construct(
        ClientBuilder $clientBuilder,
        Data $config,
        Logger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $clientBuilder
            ->setHosts($this->getConfig())
            ->setLogger($this->logger)
            ->build();
    }

    /**
     * @return array[]
     */
    private function getConfig()
    {
        return [
            [
                'host' => $this->config->getHost(),
                'post' => $this->config->getPort()
            ]
        ];
    }

    /**
     * @param array $values
     */
    public function index(array $values)
    {
        $indexName = $this->config->getIndexName();
        foreach ($values as $value) {
            $params = [
                'index' => $indexName,
                'id' => $value['url_rewrite_id'],
                'body' => $value
            ];
            $response = $this->client->index(
                $params
            );
        }
    }
}
