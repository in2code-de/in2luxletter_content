<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\Utility;

use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class ConfigurationUtility
{
    /**
     * Get TypoScript settings
     *
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    public static function getExtensionSettings(): array
    {
        return ObjectUtility::getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'in2luxletter_content'
        );
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isIndividualMailBodiesPerUserActivated(): bool
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
            'in2luxletter_content',
            'individualMailBodies'
        ) === 'user';
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isIndividualMailBodiesPerUsergroupActivated(): bool
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
            'in2luxletter_content',
            'individualMailBodies'
        ) === 'group';
    }
}
