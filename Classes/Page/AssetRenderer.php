<?php
declare(strict_types = 1);
namespace T3G\AgencyPack\Usercentrics\Page;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class AssetRenderer
 * @internal The AssetRenderer is used for the asset rendering and is not public API
 */
class AssetRenderer
{
    protected $assetCollector;

    public function __construct(AssetCollector $assetCollector = null)
    {
        $this->assetCollector = $assetCollector ?? GeneralUtility::makeInstance(AssetCollector::class);
    }

    public function renderInlineJavaScript($priority = false): string
    {
        $template = '<script%attributes%>%source%</script>';
        $assets = $this->assetCollector->getInlineJavaScripts();
        foreach ($assets as &$assetData) {
            $assetData['attributes']['type'] = $assetData['attributes']['type'] ?? 'text/javascript';
        }
        return $this->render($assets, $template, $priority);
    }

    public function renderJavaScript($priority = false): string
    {
        $template = '<script%attributes%></script>';
        $assets = $this->assetCollector->getJavaScripts();
        foreach ($assets as &$assetData) {
            $assetData['attributes']['src'] = $this->getAbsoluteWebPath($assetData['source']);
            $assetData['attributes']['type'] = $assetData['attributes']['type'] ?? 'text/javascript';
        }
        return $this->render($assets, $template, $priority);
    }

    protected function render(array $assets, string $template, bool $priority = false): string
    {
        $results = [];
        foreach ($assets as $assetData) {
            if (($assetData['options']['priority'] ?? false) !== $priority) {
                continue;
            }
            $attributes = $assetData['attributes'];
            $attributesString = count($attributes) ? ' ' . GeneralUtility::implodeAttributes($attributes, true) : '';
            $results[] = str_replace(['%attributes%', '%source%'], [$attributesString, $assetData['source']], $template);
        }
        return implode(LF, $results);
    }

    private function getAbsoluteWebPath(string $file): string
    {
        if (strpos($file, '://') !== false) {
            return $file;
        }
        return PathUtility::getAbsoluteWebPath(GeneralUtility::getFileAbsFileName($file));
    }
}
