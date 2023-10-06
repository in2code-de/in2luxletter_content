<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\Service;

// namespace GeorgRinger\LoginLink\Service;

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Georg Ringer
 */
class TokenGenerator
{
    public function generate(): string
    {
        return GeneralUtility::makeInstance(Random::class)->generateRandomHexString(40);
    }
}
