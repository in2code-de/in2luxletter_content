<?php
// phpcs:ignoreFile

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace In2code\In2luxletterContent\Routing;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Routing\UnableToLinkToPageException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Copy of TYPO3\CMS\Backend\Routing\PreviewUriBuilder which will be part of TYPO3 since v11
 *
 * @todo: Use of this class can be replaced once we drop support for TYPO3 v10
 */
class PreviewUriBuilder
{
    public const OPTION_SWITCH_FOCUS = 'switchFocus';
    public const OPTION_WINDOW_NAME = 'windowName';
    public const OPTION_WINDOW_FEATURES = 'windowFeatures';
    public const OPTION_WINDOW_SCOPE = 'windowScope';

    public const OPTION_WINDOW_SCOPE_LOCAL = 'local';
    public const OPTION_WINDOW_SCOPE_GLOBAL = 'global';

    protected $pageId;

    protected $alternativeUri;

    protected $rootLine;

    protected $section;

    protected $additionalQueryParameters;

    protected $backPath;

    protected $moduleLoading = true;

    public function __construct(int $pageId, string $alternativeUri = null)
    {
        $this->pageId = $pageId;
        $this->alternativeUri = $alternativeUri;
    }

    public static function create(int $pageId, string $alternativeUri = null): self
    {
        return GeneralUtility::makeInstance(static::class, $pageId, $alternativeUri);
    }

    public function withModuleLoading(bool $moduleLoading): self
    {
        if ($this->moduleLoading === $moduleLoading) {
            return $this;
        }
        $target = clone $this;
        $target->moduleLoading = $moduleLoading;
        return $target;
    }

    public function withRootLine(array $rootLine): self
    {
        if ($this->rootLine === $rootLine) {
            return $this;
        }
        $target = clone $this;
        $target->rootLine = $rootLine;
        return $this;
    }

    public function withSection(string $section): self
    {
        if ($this->section === $section) {
            return $this;
        }
        $target = clone $this;
        $target->section = $section;
        return $target;
    }

    public function withAdditionalQueryParameters(string $additionalQueryParameters): self
    {
        if ($this->additionalQueryParameters === $additionalQueryParameters) {
            return $this;
        }
        $target = clone $this;
        $target->additionalQueryParameters = $additionalQueryParameters;
        return $target;
    }

    public function buildDispatcherDataAttributes(array $options = null): ?array
    {
        if (null === ($attributes = $this->buildAttributes($options))) {
            return null;
        }
        $this->loadActionDispatcher();
        return $this->prefixAttributeNames('dispatch-', $attributes);
    }

    protected function buildAttributes(array $options = null): ?array
    {
        $options = $this->enrichOptions($options);
        if (null === ($uri = $this->buildUri($options))) {
            return null;
        }
        $args = [
            // target URI
            (string)$uri,
            // whether to switch focus to that window
            $options[self::OPTION_SWITCH_FOCUS],
            // name of the window instance for JavaScript references
            $options[self::OPTION_WINDOW_NAME],
        ];
        if (isset($options[self::OPTION_WINDOW_FEATURES])) {
            // optional window features (e.g. 'width=500,height=300')
            $args[] = $options[self::OPTION_WINDOW_FEATURES];
        }
        return [
            'action' => $options[self::OPTION_WINDOW_SCOPE] === self::OPTION_WINDOW_SCOPE_GLOBAL
                ? 'TYPO3.WindowManager.globalOpen'
                : 'TYPO3.WindowManager.localOpen',
            'args' => json_encode($args),
        ];
    }

    public function buildUri(array $options = null): ?Uri
    {
        try {
            $options = $this->enrichOptions($options);
            $switchFocus = $options[self::OPTION_SWITCH_FOCUS] ?? true;
            $uriString = BackendUtility::getPreviewUrl(
                $this->pageId,
                $this->backPath ?? '',
                $this->rootLine,
                $this->section ?? '',
                $this->alternativeUri ?? '',
                $this->additionalQueryParameters ?? '',
                $switchFocus
            );
            return GeneralUtility::makeInstance(Uri::class, $uriString);
        } catch (UnableToLinkToPageException $exception) {
            return null;
        }
    }

    protected function enrichOptions(array $options = null): array
    {
        return array_merge(
            [
                self::OPTION_SWITCH_FOCUS => null,
                // 'newTYPO3frontendWindow' was used in BackendUtility::viewOnClick
                self::OPTION_WINDOW_NAME => 'newTYPO3frontendWindow',
                self::OPTION_WINDOW_SCOPE => self::OPTION_WINDOW_SCOPE_LOCAL,
            ],
            $options ?? []
        );
    }

    protected function loadActionDispatcher(): void
    {
        if (!$this->moduleLoading) {
            return;
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/ActionDispatcher');
    }

    protected function prefixAttributeNames(string $prefix, array $attributes): array
    {
        $attributeNames = array_map(
            static function (string $name) use ($prefix): string {
                return $prefix . $name;
            },
            array_keys($attributes)
        );
        return array_combine(
            $attributeNames,
            array_values($attributes)
        );
    }

    public function serializeDispatcherAttributes(array $options = null): ?string
    {
        if (null === ($attributes = $this->buildDispatcherAttributes($options))) {
            return null;
        }
        return ' ' . GeneralUtility::implodeAttributes($attributes, true);
    }

    public function buildDispatcherAttributes(array $options = null): ?array
    {
        if (null === ($attributes = $this->buildAttributes($options))) {
            return null;
        }
        $this->loadActionDispatcher();
        return $this->prefixAttributeNames('data-dispatch-', $attributes);
    }

    public function buildImmediateActionElement(array $options = null): ?string
    {
        if (null === ($attributes = $this->buildAttributes($options))) {
            return null;
        }
        $this->loadImmediateActionElement();
        return sprintf(
            // `<typo3-immediate-action action="TYPO3.WindowManager.localOpen" args="[...]">`
            '<typo3-immediate-action %s></typo3-immediate-action>',
            GeneralUtility::implodeAttributes($attributes, true)
        );
    }

    protected function loadImmediateActionElement(): void
    {
        if (!$this->moduleLoading) {
            return;
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/Element/ImmediateActionElement');
    }
}
