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

class GetType extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\AdvancedHidePrice\Model\Recaptcha
     */
    protected $reCaptcha;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

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
     * GetType constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\AdvancedHidePrice\Model\Recaptcha $reCaptcha
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Bss\AdvancedHidePrice\Model\Recaptcha $reCaptcha,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->reCaptcha = $reCaptcha;
        $this->inlineTranslation = $inlineTranslation;
        $this->senderResolver = $senderResolver;
        $this->configurableData = $configurableData;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
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
     * @return \Magento\Framework\Translate\Inline\StateInterface
     */
    public function getInlineTranslation()
    {
        return $this->inlineTranslation;
    }

    /**
     * @return \Bss\AdvancedHidePrice\Model\Recaptcha
     */
    public function getRecaptcha()
    {
        return $this->reCaptcha;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return string
     */
    public function getTypeRedirect()
    {
        return \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT;
    }

    /**
     * @return string
     */
    public function getAreaFrontend()
    {
        return \Magento\Framework\App\Area::AREA_FRONTEND;
    }

    /**
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @param string $label
     * @param int $type
     * @param null $value
     * @return string
     */
    public function fieldToHtmlEmail($label, $type, $value = null)
    {
        $html = '';
        if ($type == '1' || $type == '2') { //text & area
            $html .= '<tr>';
            $html .= '<td style="font-size:12px;padding:7px 9px 9px 9px;border:1px solid #EAEAEA;">' .
                __($label) . ' : </td>';
            $html .= '<td style="font-size:12px;padding:7px 9px 9px 9px;border:1px solid #EAEAEA;">' . $value . '</td>';
        } elseif ($type =='5') {//checkbox
            $html .= '<tr>';
            $html .= '<td style="font-size:12px;padding:7px 9px 9px 9px;border:1px solid #EAEAEA;">' .
                __($label) . ' : </td>';
            if ($value == null) {
                $checked = 'No';
            } else {
                if ($value == 1) {
                    $checked = 'Yes';
                } else {
                    $checked = 'No';
                }
            }
            $html .= '<td style="font-size:12px;padding:7px 9px 9px 9px;border:1px solid #EAEAEA;">' .
                $checked . '</td>';
            $html .= '</tr>';
        } else {
            $html .= '';
        }
        return $html;
    }

    /**
     * @param string $label
     * @param int $type
     * @param null $value
     * @return string
     */
    public function fieldToHtmlBackend($label, $type, $value = null)
    {
        $html = '';
        $html .= '<div class="field">';

        if ($type == '1' || $type == '2') {//text || area
            $html .= '<span>' . $label . ': </span>';
            $html .= '<strong>' . $value . '</strong>';
        } elseif ($type == '5') {//checkbox
            $html .= '<span>' . $label . ': </span>';
            $html .= '<strong>';
            if ($value == null) {
                $checked = __('No');
            } else {
                if ($value == 1) {
                    $checked = __('Yes');
                } else {
                    $checked = __('No');
                }
            }
            $html .= $checked . '</strong>';
        } else {
            $html .="";
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }
}
