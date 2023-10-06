<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\EventListener\Luxletter;

use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\In2luxletterContent\Utility\ConfigurationUtility;
use In2code\Luxletter\Events\QueueServiceAddUserToQueueEvent;
use In2code\Luxletter\Utility\ConfigurationUtility as LuxletterConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QueueService
{
    public function addUserToQueue(QueueServiceAddUserToQueueEvent $event)
    {
        if (
            LuxletterConfigurationUtility::isAsynchronousQueueStorageActivated()
            && (ConfigurationUtility::isIndividualMailBodiesPerUserActivated()
                || ConfigurationUtility::isIndividualMailBodiesPerUsergroupActivated())
        ) {
            $parseService = GeneralUtility::makeInstance(
                NewsletterUrl::class,
                $event->getNewsletter()->getOrigin(),
                $event->getNewsletter()->getLayout(),
                $event->getUser()->getLuxletterLanguage()
            );
            $bodytext = $parseService->getParsedContent(
                $event->getNewsletter()->getConfiguration()->getSiteConfiguration(),
                $event->getUser()
            );
            $queue = $event->getQueue();
            $queue->setBodytext($bodytext);

            $event->setQueue($queue);
        }
    }
}
