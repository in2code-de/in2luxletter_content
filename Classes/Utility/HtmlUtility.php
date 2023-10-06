<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\Utility;

use DOMDocument;
use DOMXpath;
use Exception;

class HtmlUtility
{
    /**
     * @param string $string
     * @return string
     */
    public static function getBodyFromHtml(string $string): string
    {
        try {
            $document = new DOMDocument();
            libxml_use_internal_errors(true);
            @$document->loadHtml($string);
            libxml_use_internal_errors(false);
            $xpath = new DOMXpath($document);
            $result = '';
            foreach ($xpath->evaluate('//body/node()') as $node) {
                $result .= $document->saveHtml($node);
            }
            if (!empty($result)) {
                return $result;
            }
        } catch (Exception $exception) {
            return $string;
        }
        return $string;
    }
}
