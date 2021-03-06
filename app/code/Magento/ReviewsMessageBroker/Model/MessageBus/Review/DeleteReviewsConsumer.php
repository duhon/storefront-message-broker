<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ReviewsMessageBroker\Model\MessageBus\Review;

use Magento\MessageBroker\Model\ServiceConnector\Connector;
use Magento\ReviewsMessageBroker\Model\ServiceConfig;
use Magento\ReviewsStorefrontApi\Api\Data\DeleteReviewsRequestMapper;
use Magento\ReviewsMessageBroker\Model\MessageBus\ConsumerEventInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete reviews from storage
 */
class DeleteReviewsConsumer implements ConsumerEventInterface
{
    /**
     * @var DeleteReviewsRequestMapper
     */
    private $deleteReviewsRequestMapper;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DeleteReviewsRequestMapper $deleteReviewsRequestMapper
     * @param Connector $connector
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteReviewsRequestMapper $deleteReviewsRequestMapper,
        Connector $connector,
        LoggerInterface $logger
    ) {
        $this->deleteReviewsRequestMapper = $deleteReviewsRequestMapper;
        $this->connector = $connector;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $entities, string $scope = null): void
    {
        $ids = [];

        foreach ($entities as $entity) {
            $ids[] = $entity->getEntityId();
        }

        $deleteRequest = $this->deleteReviewsRequestMapper->setData(['reviewIds' => $ids])->build();
        $result = $this->connector
            ->getConnection(ServiceConfig::SERVICE_NAME_REVIEWS)
            ->deleteProductReviews($deleteRequest);

        if ($result->getStatus() === false) {
            $this->logger->error(\sprintf('Reviews deletion has failed: "%s"', $result->getMessage()));
        }
    }
}
