<?php

$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = true;
$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = true;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'in2luxletter_content',
    'auth',
    \In2code\In2luxletterContent\Authentication\TokenAuthenticationService::class,
    [
        'title' => 'FE-User authentication',
        'description' => 'Authentication with token.',
        'subtype' => 'getUserFE,authUserFE',
        'available' => true,
        'priority' => 80,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => \In2code\In2luxletterContent\Authentication\TokenAuthenticationService::class
    ]
);
