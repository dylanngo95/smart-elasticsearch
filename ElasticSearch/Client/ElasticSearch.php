<?php

declare(strict_types=1);

namespace Smart\UrlRewriteIndex\ElasticSearch\Client;


use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Smart\UrlRewriteIndex\Helper\Data;
use Smart\UrlRewriteIndex\Logger\Logger;

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
     * @var string
     */
    private $indexId = '1';

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
    )
    {
        $this->client = $clientBuilder->build();
        $this->config = $config;
        $this->logger = $logger;
    }

    public function index($values)
    {
        foreach ($values as $value) {
            $params = [
                'index' => $this->config->getIndexName(),
                'id' => $this->indexId,
                'body' => $value
            ];
            $response = $this->client->index(
                $params
            );
            $this->logger->info(print_r($response, true));
        }
    }
}
