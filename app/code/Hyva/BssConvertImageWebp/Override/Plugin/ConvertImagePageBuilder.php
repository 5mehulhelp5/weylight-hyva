<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Hyva_BssConvertImageWebp
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Hyva\BssConvertImageWebp\Override\Plugin;

use DOMDocument;
use Bss\ConvertImageWebp\Model\Config;
use Bss\ConvertImageWebp\Model\ReplaceUrl;
use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Framework\View\ConfigInterface;

class ConvertImagePageBuilder extends \Hyva\Theme\Plugin\PageBuilder\OverrideTemplatePlugin
{
    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ReplaceUrl
     */
    protected $replaceUrl;

    /**
     * @param CurrentTheme $theme
     * @param ConfigInterface $viewConfig
     * @param Config $config
     * @param ReplaceUrl $replaceUrl
     */
    public function __construct(
        CurrentTheme $theme,
        MathRandom $mathRandom,
        ConfigInterface $viewConfig,
        Config $config,
        ReplaceUrl $replaceUrl
    )
    {
        parent::__construct($theme, $mathRandom);
        $this->config = $config;
        $this->replaceUrl = $replaceUrl;
    }

    /**
     * Copy core
     *
     * @param string $htmlContent
     * @return string
     * @throws \DOMException
     */
    protected function applyFilters(string $htmlContent): string
    {
        // Validate if the filtered result requires background image processing

        if (preg_match_all(self::BACKGROUND_IMAGE_PATTERN, $htmlContent, $matches, PREG_SET_ORDER)) {
            // Each match contains two keys:
            // 0 => the entire matching snippet
            // 1 => the first regex group match, in this case the json object with bg image data
            $htmlContent = $this->generateBackgroundImageStyles($htmlContent, $matches);
        }

        // Validate if the filtered result requires html decoding
        if (preg_match_all(self::HTML_CONTENT_TYPE_PATTERN, $htmlContent, $matches, PREG_SET_ORDER)) {
            $htmlContent = $this->decodeHtmlContent($htmlContent, $matches);
        }

        return $htmlContent;
    }

    /**
     * Copy core
     *
     * @param string $htmlContent
     * @param array $htmlContentMatches
     * @return string
     */
    private function decodeHtmlContent(string $htmlContent, array $htmlContentMatches): string
    {
        foreach ($htmlContentMatches as $htmlContentMatch) {
            //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $decodedHtml = html_entity_decode($htmlContentMatch[0]);

            $htmlContent = str_replace($htmlContentMatch[0], $decodedHtml, $htmlContent);
        }

        return $htmlContent;
    }

    /**
     * Generate Background Image Styles
     *
     * @param string $htmlContent
     * @param array $backgroundMatches
     * @return string
     * @throws \DOMException
     */
    private function generateBackgroundImageStyles(string $htmlContent, array $backgroundMatches): string
    {
        $domDocument = new \DomDocument();
        $generatedStyles = '';
        $arr = [];
        $isEnablePageBuilder = $this->config->isEnableModule() && $this->config->isEnableImagePageBuilder();
        foreach ($backgroundMatches as $backgroundMatch) {

            // input: {\"desktop_image\":\"imagepath.jpg\", \"mobile_image\": \"imagepath.jpg\"}"
            // output: ["desktop_image" => "imagepath.jpg", "mobile_imag" => "imagepath.jpg"];
            //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $backgroundImages = json_decode(stripslashes(html_entity_decode($backgroundMatch[1])), true);

            if (!empty($backgroundImages)) {
                $id = uniqid();
                $dataAttribute = 'data-image-id="' . $id . '"';

                // generate responsive styles with the data-image-id as CSS selector
                if ($isEnablePageBuilder) { // Works by Bss
                    $backgroundImageWebp = $this->replaceUrl->replaceUrl($backgroundImages['desktop_image']);
                    $arr[$id]['webp'] = $backgroundImageWebp;
                    $arr[$id]['default'] = $backgroundImages['desktop_image'];
                } else { // Works by default
                    $cssDataAttributeSelector = '[' . $dataAttribute . ']';
                    $generatedStyles .= $this->generateCssFromImages($cssDataAttributeSelector, $backgroundImages);
                }

                // replace the data-background-images attribute with a unique data-image-id="id" attribute
                $htmlContent = str_replace($backgroundMatch[0], $dataAttribute, $htmlContent);
            }
        }
        $obj = json_encode($arr);

        if ($isEnablePageBuilder) { // Works by Bss
            $styleElementScript = $this->addScriptChangeWebp($domDocument, $obj);
            $styleElementScript->setAttribute('type', 'text/javascript');
            $domDocument->appendChild($styleElementScript);
        }

        // create a <style> tag, insert the generated CSS string
        $styleElement = $domDocument->createElement(
            'style',
            $generatedStyles
        );
        $styleElement->setAttribute('type', 'text/css');
        $domDocument->appendChild($styleElement);

        // append the generated <style/> tag to the htmlContent
        return $htmlContent . $domDocument->saveHTML();
    }

    /**
     * Add Script Change Image Webp
     *
     * @param $domDocument
     * @param $obj
     * @return mixed
     */
    public function addScriptChangeWebp($domDocument, $obj)
    {
        return $domDocument->createElement(
            'script',
            <<<script
                window.addEventListener('load', function () {
                  var arr = {$obj};
                    if (window.canDisplayWebp) {
                        for (const [key, val] of Object.entries(arr)) {
                            document.querySelector('[data-image-id="' + key + '"]').style.backgroundImage =
                            "url('" + val.webp + "')";
                        }
                    } else {
                        for (const [key, val] of Object.entries(arr)) {
                            document.querySelector('[data-image-id="' + key + '"]').style.backgroundImage =
                            "url('" + val.default + "')";
                        }
                    }
                })
            script
        );
    }

    /**
     * Copy core
     *
     * @param string $elementClass
     * @param array $images
     * @return string
     */
    private function generateCssFromImages(string $elementClass, array $images): string
    {
        $css = [];
        if (isset($images['desktop_image'])) {
            $css[$elementClass] = [
                'background-image' => 'url(' . $images['desktop_image'] . ')',
            ];
        }
        if (isset($images['mobile_image']) && $this->getMediaQuery('mobile')) {
            $css[$this->getMediaQuery('mobile')][$elementClass] = [
                'background-image' => 'url(' . $images['desktop_image'] . ')',
            ];
        }
        if (isset($images['mobile_image']) && $this->getMediaQuery('mobile-small')) {
            $css[$this->getMediaQuery('mobile-small')][$elementClass] = [
                'background-image' => 'url(' . $images['desktop_image'] . ')',
            ];
        }
        return $this->cssFromArray($css);
    }

    /**
     * Copy core
     *
     * @param string $view
     * @return string|null
     */
    private function getMediaQuery(string $view): ?string
    {
        $breakpoints = $this->viewConfig->getViewConfig()->getVarValue(
            'Magento_PageBuilder',
            'breakpoints/' . $view . '/conditions'
        );
        if ($breakpoints && count($breakpoints) > 0) {
            $mobileBreakpoint = '@media only screen ';
            foreach ($breakpoints as $key => $value) {
                $mobileBreakpoint .= 'and (' . $key . ': ' . $value . ') ';
            }
            return rtrim($mobileBreakpoint);
        }
        return null;
    }

    /**
     * Copy core
     *
     * @param array $css
     * @return string
     */
    private function cssFromArray(array $css): string
    {
        $output = '';
        foreach ($css as $selector => $body) {
            if (is_array($body)) {
                $output .= $selector . ' {';
                $output .= $this->cssFromArray($body);
                $output .= '}';
            } else {
                $output .= $selector . ': ' . $body . ';';
            }
        }
        return $output;
    }
}
