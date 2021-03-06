<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogMessageBroker\Model\MessageBus\ProductVariants;

use Magento\CatalogExport\Event\Data\ChangedEntities;
use Magento\CatalogExport\Event\Data\Entity;
use Magento\CatalogMessageBroker\Model\MessageBus\ConsumerEventInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Process product variants messages
 */
class ProductVariantsConsumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConsumerEventInterfaceFactory
     */
    private $consumerEventFactory;

    /**
     * @param LoggerInterface $logger
     * @param ConsumerEventInterfaceFactory $consumerEventFactory
     */
    public function __construct(
        LoggerInterface $logger,
        ConsumerEventInterfaceFactory $consumerEventFactory
    ) {
        $this->logger = $logger;
        $this->consumerEventFactory = $consumerEventFactory;
    }

    /**
     * Process message
     *
     * @param ChangedEntities $message
     * @return void
     */
    public function processMessage(ChangedEntities $message): void
    {
        try {
            $eventType = $message->getMeta() ? $message->getMeta()->getEventType() : null;
            $entities = $message->getData() ? $message->getData()->getEntities() : null;

            if (empty($entities)) {
                throw new \InvalidArgumentException('Product variants data is missing in payload');
            }

            $variantsEvent = $this->consumerEventFactory->create($eventType);
            $variantsEvent->execute($entities);
        } catch (\Throwable $e) {
            $this->logger->error(
                \sprintf(
                    'Unable to process collected product variants data. Event type: "%s", ids:  "%s"',
                    $eventType ?? '',
                    \implode(',', \array_map(function (Entity $entity) {
                        return $entity->getEntityId();
                    }, $entities ?? []))
                ),
                ['exception' => $e]
            );
        }
    }
}
