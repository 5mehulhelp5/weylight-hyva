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
 * @package    Bss_Gallery
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Plugin\Adminhtml\Data\Form\Element;

/**
 * Class Fieldset
 *
 * @package Bss\Gallery\Plugin\Adminhtml\Data\Form\Element
 */
class Fieldset
{
    /**
     * Before add file
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $subject
     * @param string $elementId
     * @param string $type
     * @param array $config
     * @param bool $after
     * @param bool $isAdvanced
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddField($subject, $elementId, $type, $config, $after = false, $isAdvanced = false)
    {
        $validate = " validate-number validate-digits validate-greater-than-zero required-entry time-bss-gallery";
        if (isset($config['class']) && isset($config['name'])) {
            if (strstr($config['name'], 'bss_gallery_slider_autoplay_timeout')) {
                $config['class'] = $config['class'] . $validate;
            }
        }
        return [
            $elementId,
            $type,
            $config,
            $after,
            $isAdvanced
        ];
    }
}
