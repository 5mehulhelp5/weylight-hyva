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
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Helper;

class HelperData extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableData;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * HelperData constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct($context);
        $this->senderResolver = $senderResolver;
        $this->configurableData = $configurableData;
        $this->httpContext = $httpContext;
    }

    /**
     * @return \Magento\Framework\App\Http\Context
     */
    public function getHttpContext()
    {
        return $this->httpContext;
    }

    /**
     * @return \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    public function getConfigurableData()
    {
        return $this->configurableData;
    }

    /**
     * @return \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    public function getSenderResovler()
    {
        return $this->senderResolver;
    }

    /**
     * @param array $field
     * @param null $value
     * @return string
     */
    public function fieldToHtmlFrontend($key, $field, $value = null)
    {
        $html = '';
        $required = isset($field['field_required']) ? 'data-validate="{required:true}"' : '';
        if (isset($field['field_required']) && $field['field_required']) {
            $html .= '<div class="field required">';
        } else {
            $html .= '<div class="field">';
        }

        if ($field['field_type'] == '1') {
            //text
            $html .= '<label class="label" for="'.$key.'">'.__($field['field_label']).'</label>';
            $html .= '<div class="control">';
            $html .= '<input type="text" name="' . $key .'"'.
                $required . ' class="input-text" value="' . $value . '"/>';
        } elseif ($field['field_type'] == '2') {
            //area
            $html .= '<label class="label" for="' . $key . '">' .
                __($field['field_label']) . '</label>';
            $html .= '<div class="control">';
            $html .= '<textarea name="' . $key . '"' . $required
                . ' class="input-text"/>' . $value . '</textarea>';
        } elseif ($field['field_type'] == '5') {
            // checkbox
            $html .= '<label class="label label-checkbox" for="'
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
            $html .= '<input class="checkbox" type="checkbox" name="'
                . $key . '"' . $required . ' value="1"' . $checked . '/>';
        } else {
            $html .="";
        }

        $html .= '</div></div>';
        return $html;
    }
}
