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
 * @category  BSS
 * @package   Hyva_BssAdvancedHidePrice
 * @author    Extension Team
 * @copyright Copyright (c) 2023-present BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Hyva\BssAdvancedHidePrice\Helper;

use Bss\AdvancedHidePrice\Helper\HelperData as HelperOrigin;

// phpcs:disable Generic.Files.LineLength.TooLong

class HelperData extends HelperOrigin
{
    /**
     * Get html field
     *
     * @param string $key
     * @param array $field
     * @param mixed|null $value
     * @return string
     */
    public function fieldToHtmlFrontend($key, $field, $value = null)
    {
        $html = '';
        $requiredAttr = '';
        if (isset($field['field_required']) && $field['field_required']) {
            $requiredAttr = 'required';
            $html .= '<div class="field required mb-5">';
        } else {
            $html .= '<div class="field mb-5">';
        }

        if ($field['field_type'] == '1') {
            //text
            $html .= '<label class="label font-semibold" for="'.$key.'">'.__($field['field_label']).'</label>';
            $html .= '<div class="control">';
            $html .= '<input type="text" name="' . $key .'" class="input-text w-full border-gray-300 rounded-sm" value="' . $value . '" ' . $requiredAttr . ' />';
        } elseif ($field['field_type'] == '2') {
            //area
            $html .= '<label class="label font-semibold" for="' . $key . '">' .
                __($field['field_label']) . '</label>';
            $html .= '<div class="control">';
            $html .= '<textarea ' . $requiredAttr . ' name="' . $key . '" class="input-text w-full border-gray-300 rounded-sm"/>' . $value . '</textarea>';
        } elseif ($field['field_type'] == '5') {
            // checkbox
            $html .= '<label class="label label-checkbox font-semibold" for="'
                . $key . '">' . __($field['field_label']) . '</label>';
            $html .= '<div class="control control-checkbox">';
            if ($value == null) {
                $html .= '<input type="hidden" name="' . $key . '" value="0"/>';
                $checked = '';
            } else {
                if ($value == 1) {
                    $checked = 'checked="checked"';
                } else {
                    $checked = '';
                }
                $html .= '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
            }
            $html .= '<input class="checkbox border-gray-300 rounded-sm" type="checkbox" name="'
                . $key . '" value="1"' . $checked . ' ' . $requiredAttr . ' />';
        } else {
            $html .="";
        }

        $html .= '</div></div>';
        return $html;
    }
}
