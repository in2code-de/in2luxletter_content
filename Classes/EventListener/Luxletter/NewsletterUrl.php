<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\EventListener\Luxletter;

use In2code\In2luxletterContent\Repository\TokenRepository;
use In2code\In2luxletterContent\Routing\PreviewUriBuilder;
use In2code\In2luxletterContent\Service\TokenGenerator;
use In2code\In2luxletterContent\Utility\ConfigurationUtility;
use In2code\In2luxletterContent\Utility\HtmlUtility;
use In2code\Luxletter\Domain\Service\RequestService;
use In2code\Luxletter\Events\NewsletterUrlGetContentFromOriginEvent;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility as LuxletterConfigurationUtility;
use Throwable;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NewsletterUrl
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getContentUserSpecific(NewsletterUrlGetContentFromOriginEvent $event)
    {
        if (
            LuxletterConfigurationUtility::isAsynchronousQueueStorageActivated()
            && ConfigurationUtility::isIndividualMailBodiesPerUserActivated()
        ) {
            $targetPage = (int)$event->getNewsletterUrl()->getOrigin();
            if (!$targetPage) {
                return '';
            }

            $string = $this->getHtml($event, $targetPage);

            $event->setString($string);
        }
    }

    public function getContentUsergroupSpecific(NewsletterUrlGetContentFromOriginEvent $event)
    {
        if (
            LuxletterConfigurationUtility::isAsynchronousQueueStorageActivated()
            && ConfigurationUtility::isIndividualMailBodiesPerUsergroupActivated()
        ) {
            $targetPage = (int)$event->getNewsletterUrl()->getOrigin();
            if (!$targetPage) {
                return '';
            }

            $string = $this->getHtml($event, $targetPage);

            $event->setString($string);
        }
    }

    /**
     * @param NewsletterUrlGetContentFromOriginEvent $event
     * @param int $targetPage
     * @return string
     */
    protected function getHtml(NewsletterUrlGetContentFromOriginEvent $event, int $targetPage): string
    {
        $cacheIdentifier = 'p' . $targetPage;
        if (ConfigurationUtility::isIndividualMailBodiesPerUserActivated()) {
            $cacheIdentifier .= '-u' . $event->getUser()->getUid();
        } elseif (ConfigurationUtility::isIndividualMailBodiesPerUsergroupActivated()) {
            $usergroupUids = [];
            foreach ($event->getUser()->getUsergroup() as $usergroup) {
                $usergroupUids[] = $usergroup->getUid();
            }
            sort($usergroupUids);
            $cacheIdentifier .= '-ug' . implode('_', $usergroupUids);
        }

        if (($string = $this->cache->get($cacheIdentifier)) === false) {
            $tags = ['in2luxletter_content', 'pageId_' . $targetPage];
            $lifetime = 60 * 60 * 24;

            $string = $this->getUncachedHtml($event, $targetPage);

            $this->cache->set($cacheIdentifier, $string, $tags, $lifetime);
        }
        return $string;
    }

    /**
     * @param NewsletterUrlGetContentFromOriginEvent $event
     * @param int $targetPage
     * @return string
     */
    protected function getUncachedHtml(NewsletterUrlGetContentFromOriginEvent $event, int $targetPage): string
    {
        $tokenGenerator = GeneralUtility::makeInstance(TokenGenerator::class);
        $tokenRepository = GeneralUtility::makeInstance(TokenRepository::class);

        $recordId = $event->getUser()->getUid();
        $authType = 'fe';
        $invokedBy = 0;

        $token = $tokenGenerator->generate();
        $tokenRepository->add(
            $recordId,
            $authType,
            $token,
            $invokedBy
        );

        // @TODO: language-Handling in URL (and cacheIdentifier)

        $typenum = LuxletterConfigurationUtility::getTypeNumToNumberLocation();
        if ($typenum > 0) {
            $typenum = '&type=' . $typenum;
        }
        $url = (string)PreviewUriBuilder::create($targetPage)
            ->withAdditionalQueryParameters('&byToken=' . $token . $typenum)
            ->buildUri();

        if ($url === '') {
            throw new InvalidUrlException('Given URL was invalid and was not parsed', 1560709687);
        }
        $requestService = GeneralUtility::makeInstance(RequestService::class);
        try {
            $string = $requestService->getContentFromUrl($url);
        } catch (Throwable $exception) {
            throw new MisconfigurationException(
                'Given URL could not be parsed and accessed (Tried to read url: ' . $url
                . '). Typenum definition in site-configuration not set? Fluid Styled Mail Content TypoScript added?',
                1560709791
            );
        }
        return HtmlUtility::getBodyFromHtml($string);
    }
}
