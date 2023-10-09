<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'User-specific content for LUXletter',
    'description' => 'User-specific content for LUXletter',
    'category' => 'misc',
    'author' => 'Julian Hofmann',
    'author_email' => 'julian.hofmann@in2code.de',
    'state' => 'alpha',
    'version' => '0.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            "luxletter" => "17.7.0-17.99.99",
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'In2code\\In2luxletterContent\\' => 'Classes',
        ],
    ],
];
