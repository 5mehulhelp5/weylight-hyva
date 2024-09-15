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

namespace Bss\AdvancedHidePrice\Block;

use Bss\AdvancedHidePrice\Helper\Data as Helper;
use Bss\AdvancedHidePrice\Model\ResourceModel\FormFields\CollectionFactory as FormFieldsCollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\Resolver;

class Popup extends Template
{
    /**
     * @var array
     */
    protected $languages = [
        'ar_DZ|ar_SA|ar_KW|ar_MA|ar_EG|az_AZ|' => 'ar',
        'bg_BG' => 'bg',
        'ca_ES' => 'ca',
        'zh_CN' => 'zh-CN',
        'zh_HK|zh_TW' => 'zh-TW',
        'hr_HR' => 'hr',
        'cs_CZ' => 'cs',
        'da_DK' => 'da',
        'nl_NL' => 'nl',
        'en_GB|en_AU|en_NZ|en_IE|cy_GB' => 'en-GB',
        'en_US|en_CA' => 'en',
        'fil_PH' => 'fil',
        'fi_FI' => 'fi',
        'fr_FR' => 'fr',
        'fr_CA' => 'fr-CA',
        'de_DE' => 'de',
        'de_AT)' => 'de-AT',
        'de_CH' => 'de-CH',
        'el_GR' => 'el',
        'he_IL' => 'iw',
        'hi_IN' => 'hi',
        'hu_HU' => 'hu',
        'gu_IN|id_ID' => 'id',
        'it_IT|it_CH' => 'it',
        'ja_JP' => 'ja',
        'ko_KR' => 'ko',
        'lv_LV' => 'lv',
        'lt_LT' => 'lt',
        'nb_NO' => 'no',
        'fa_IR' => 'fa',
        'pl_PL' => 'pl',
        'pt_BR' => 'pt-BR',
        'pt_PT' => 'pt-PT',
        'ro_RO' => 'ro',
        'ru_RU' => 'ru',
        'sr_RS' => 'sr',
        'sk_SK' => 'sk',
        'sl_SI' => 'sl',
        'es_ES|gl_ES' => 'es',
        'es_AR|es_CL|es_CO|es_CR|es_MX|es_PA|es_PE|es_VE' => 'es-419',
        'sv_SE' => 'sv',
        'th_TH' => 'th',
        'tr_TR' => 'tr',
        'uk_UA' => 'uk',
        'vi_VN' => 'vi'
    ];

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Resolver
     */
    protected $locateResolver;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetaData;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var Product\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var Option\Value
     */
    protected $value;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var
     */
    protected $productOption;

    /**
     * Popup constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Registry $registry
     * @param Helper $helper
     * @param Session $customerSession
     * @param Resolver $locateResolver
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ProductMetadataInterface $productMetaData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param Product\OptionFactory $optionFactory
     * @param Option\Value $value
     * @param Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        Registry $registry,
        Helper $helper,
        Session $customerSession,
        Resolver $locateResolver,
        \Magento\Framework\UrlInterface $urlBuilder,
        ProductMetadataInterface $productMetaData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Catalog\Model\Product\Option\Value $value,
        Data $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->locateResolver = $locateResolver;
        $this->urlBuilder = $urlBuilder;
        $this->productMetaData = $productMetaData;
        $this->registry = $registry;
        $this->objectManager = $objectManager;
        $this->productFactory = $productFactory;
        $this->optionFactory = $optionFactory;
        $this->value = $value;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return bool
     */
    public function isShowCustomerNameInfo()
    {
        $config = $this->helper->isShowCustomerNameInfo();
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn && !$config) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return mixed
     */
    public function getSiteKey()
    {
        return $this->helper->getSiteKey();
    }

    /**
     * @return string
     */
    public function getRecaptchaScript()
    {
        $language = $this->locateResolver->getLocale();

        $lang = 'en';

        foreach ($this->languages as $options => $value) {
            if (stristr($options, $language)) {
                $lang = $value;
            }
        }

        $query = 'onload=onloadCallback&render=explicit&hl=' . $lang;

        return "https://www.google.com/recaptcha/api.js?" . $query;
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->urlBuilder->getUrl('callforprice/request/send');
    }

    /**
     * @return mixed
     */
    public function compareVersion()
    {
        $version = $this->productMetaData->getVersion();
        return version_compare($version, '2.3.1', '<');
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Collection
     */
    public function getCustomOption()
    {
        if (!$this->productOption) {
            $id = $this->getRequest()->getParam('id');
            $product = $this->productFactory->create()->load($id);
            $this->productOption = $this->optionFactory->create()->getProductOptions($product);
        }
        return $this->productOption;
    }
    /**
     * @return mixed
     */
    public function getOptions()
    {
        $customOption = $this->getCustomOption();
        foreach ($customOption as $option) {
            $data[$option->getOptionId()] = $option->getTitle();
        }
        if (!empty($data)) {
            return $this->jsonHelper->jsonEncode($data);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getOptionType()
    {
        $customOption =  $this->getCustomOption();
        foreach ($customOption as $option) {
            $value = $this->value->getValuesCollection($option);
            foreach ($value as $item) {
                $data[$item->getOptionTypeId()] = $item->getTitle();
            }
        }
        if (!empty($data)) {

            return $this->jsonHelper->jsonEncode($data);
        }
        return false;
    }
}
