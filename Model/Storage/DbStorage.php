<?php

declare(strict_types=1);

namespace Smart\ElasticSearch\Model\Storage;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Psr\Log\LoggerInterface;

class DbStorage extends \Magento\UrlRewrite\Model\Storage\DbStorage
{

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceConnection $resource
     * @param LoggerInterface|null $logger
     * @param EventManager $eventManager
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource,
        LoggerInterface $logger = null,
        EventManager $eventManager
    ) {
        parent::__construct($urlRewriteFactory, $dataObjectHelper, $resource, $logger);
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritDoc
     */
    protected function doReplace(array $urls): array
    {
        $result = parent::doReplace($urls);
        $this->eventManager->dispatch('url_rewrite_save_after', ['url' => $result]);
        return $result;
    }
}
