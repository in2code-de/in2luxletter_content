<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();

$config->setRules([
    'no_trailing_comma_in_singleline_array' => true,
]);
$config->getFinder()->in('Classes')->in('Configuration');
return $config;
