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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Bss\AdvancedHidePrice\Helper
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CALL_FOR_PRICE = 'callforprice';
    const HIDE_PRICE = 'hideprice';
    const DISABLE_ADVANCEDHIDEPRICE = 'disable';
    const NO_SELECT_OPTION = 'notselect';
    const XML_PATH_ENABLED = 'bss_call_for_price/general/enable';
    const XML_PATH_SELECTOR = 'bss_call_for_price/general/selector';
    const XML_PATH_CALL_HIDE_PRICE_PRIORITY = 'bss_call_for_price/priority/priority';
    const XML_CALL_FOR_PRICE_CATEGORIES = 'bss_call_for_price/callforprice_global/categories';
    const XML_CALL_FOR_PRICE_CUSTOMERS = 'bss_call_for_price/callforprice_global/customers';
    const XML_CALL_FOR_PRICE_NOT_APLLY = 'bss_call_for_price/callforprice_global/not_apply';
    const XML_HIDE_PRICE_CATEGORIES = 'bss_call_for_price/hideprice_global/categories';
    const XML_HIDE_PRICE_CUSTOMERS = 'bss_call_for_price/hideprice_global/customers';
    const XML_HIDE_PRICE_NOT_APPLY = 'bss_call_for_price/hideprice_global/not_apply';
    const XML_ADMIN_EMAIL_NOTIFY = 'bss_call_for_price/general/admin_notify_email';
    const XML_ADMIN_EMAIL_SENDER = 'bss_call_for_price/general/email_sender';
    const XML_PATH_CALL_FOR_PRICE_TEXT = 'bss_call_for_price/callforprice_global/text';
    const XML_PATH_HIDE_PRICE_TEXT = 'bss_call_for_price/hideprice_global/text';
    const XML_PATH_ADMIN_NOTIFY_EMAIL_TEMPLATE = 'bss_call_for_price/general/admin_notify_email_template';
    const XML_PATH_ADMIN_RESPONSE_EMAIL_TEMPLATE = 'bss_call_for_price/general/admin_response_email_template';
    const XML_PATH_IS_SHOW_CUSTOMER_FIELDS = 'bss_call_for_price/callforprice_design/is_show_customer_fields';
    const XML_PATH_CALL_FOR_PRICE_ANTI_SPAM = 'bss_call_for_price/callforprice_design/recaptcha';
    const XML_PATH_CALL_FOR_PRICE_SITE_KEY = 'bss_call_for_price/callforprice_design/site_key';
    const XML_PATH_CALL_FOR_PRICE_SECRET_KEY = 'bss_call_for_price/callforprice_design/secret_key';
    const USE_CONFIG_GLOBAL = 0;
    const USE_CONFIG_CALL_PRICE = 1;
    const USE_CONFIG_HIDE_PRICE = 2;
    const USE_CONFIG_DISABLE = 3;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param HelperData $helperData
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Bss\AdvancedHidePrice\Helper\HelperData $helperData,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->productRepository = $pr;
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->serializer = $serializer;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->json = $json;
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function returnJson()
    {
        return $this->json;
    }

    /**
     * @return \Magento\Framework\Serialize\SerializerInterface
     */
    public function returnSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return string
     */
    public function getAdminNotifyEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_NOTIFY_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getAdminResponseEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_RESPONSE_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getSiteKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALL_FOR_PRICE_SITE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALL_FOR_PRICE_SECRET_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function enableAntispam()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALL_FOR_PRICE_ANTI_SPAM,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param array $field
     * @param string|null $value
     * @return string
     */
    public function fieldToHtmlFrontend($key, $field, $value = null)
    {
        return $this->helperData->fieldToHtmlFrontend($key, $field, $value);
    }

    /**
     * @param int $store
     * @return bool
     */
    public function isShowCustomerNameInfo($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_SHOW_CUSTOMER_FIELDS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function getPriority($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALL_HIDE_PRICE_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return bool
     */
    public function isEnable($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function getSelector($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELECTOR,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function getCallForPriceCategories($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_CALL_FOR_PRICE_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function getCallForPriceCustomers($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_CALL_FOR_PRICE_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function getHidePriceCategories($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_HIDE_PRICE_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function getHidePriceCustomers($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_HIDE_PRICE_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function callForPriceNotApply($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_CALL_FOR_PRICE_NOT_APLLY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return string
     */
    public function hidePriceNotApply($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_HIDE_PRICE_NOT_APPLY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param object $product
     * @return mixed
     */
    public function getTypeIsUsedByProduct($product)
    {
        return $product->getCallforpriceType();
    }

    /**
     * @param int $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductById($id)
    {
        return $this->productRepository->getById($id, false);
    }

    /**
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @param object $product
     * @return mixed|string
     */
    public function getCallforpriceText($product)
    {
        if ($product->getCallforpriceText() && (bool)$this->getTypeIsUsedByProduct($product)) {
            return $product->getCallforpriceText();
        } else {
            if ($this->activeCallHidePrice($product) == self::CALL_FOR_PRICE) {
                return $this->scopeConfig->getValue(
                    self::XML_PATH_CALL_FOR_PRICE_TEXT,
                    ScopeInterface::SCOPE_STORE
                );
            } elseif ($this->activeCallHidePrice($product) == self::HIDE_PRICE) {
                return $this->scopeConfig->getValue(
                    self::XML_PATH_HIDE_PRICE_TEXT,
                    ScopeInterface::SCOPE_STORE
                );
            } else {
                return '';
            }
        }
    }

    /**
     * @return string
     */
    public function getAdminEmailNotify()
    {
        return $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_NOTIFY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getEmailSender()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResovler()->resolve($from);
        return $result['email'];
    }

    /**
     * @return string
     */
    public function getEmailSenderName()
    {
        $from = $this->scopeConfig->getValue(
            self::XML_ADMIN_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
        $result = $this->helperData->getSenderResovler()->resolve($from);
        return $result['name'];
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        $isLoggedIn = $this->helperData->getHttpContext()->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn) {
            return $this->getCustomerSession()->getCustomer()->getGroupId();
        }
        return 0;
    }

    /**
     * @param string $string
     * @return array
     */
    public function filterArray($string)
    {
        if ($string !== null) {
            $array = explode(',', $string);
        } else {
            return [];
        }

        return array_filter($array, function ($value) {
            return $value !== '';
        });
    }

    /**
     * Check call hide price to configurable product
     * @param mixed $parentProduct
     * @param int $storeId
     * @param int $customerGroup
     * @return bool|string
     */
    public function activeCallHidePriceConfigurable($parentProduct, $storeId = null, $customerGroup = null)
    {
        if ($parentProduct && $this->isEnable($storeId)) {
            if ($this->getTypeIsUsedByProduct($parentProduct) != self::USE_CONFIG_DISABLE) {
                if ($this->getTypeIsUsedByProduct($parentProduct) == self::USE_CONFIG_GLOBAL) {
                    return $this->isActiveGlobal($parentProduct, $storeId, $customerGroup);
                }
                return $this->isActiveProduct($parentProduct, $customerGroup);
            }
        }
        return false;
    }

    /**
     * @param object $product
     * @param int $storeId
     * @param int $customerGroup
     * @return bool|string
     */
    public function activeCallHidePrice($product, $storeId = null, $customerGroup = null)
    {
        if ($product && $this->isEnable($storeId)) {
            if ($this->isActiveProduct($product, $customerGroup) == self::DISABLE_ADVANCEDHIDEPRICE) {
                return false;
            }
            if ($this->isActiveProduct($product, $customerGroup)) {
                if ($this->isActiveProduct($product, $customerGroup) !== self::NO_SELECT_OPTION) {
                    return $this->isActiveProduct($product, $customerGroup);
                }
                return $this->isActiveGlobal($product, $storeId, $customerGroup);
            }
        }
        return false;
    }

    /**
     * @param int $productEntityId
     * @return array
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        $map_r = [];
        $parentProduct = $this->helperData->getConfigurableData()->getChildrenIds($productEntityId);
        $product = $this->productRepository->getById($productEntityId);
        $parentAttribute = $this->helperData->getConfigurableData()->getConfigurableAttributes($product);
        $result['entity'] = $productEntityId;
        $attributes = $product->getAttributes();
        $checkCallHidePriceParent = $this->activeCallHidePriceConfigurable($product);
        foreach ($parentAttribute as $attrKey => $attrValue) {
            $attributeCode = $attrValue->getProductAttribute()->getAttributeCode();
            if (isset($attributes[$attributeCode])
                && $attributes[$attributeCode]->getOptions()
            ) {
                foreach ($attributes[$attributeCode]->getOptions() as $tvalue) {
                    $result['map'][$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                    $result['map'][$attrValue->getAttributeId()][$tvalue->getValue()] = $tvalue->getLabel();
                    $map_r[$attrValue->getAttributeId()][$tvalue->getLabel()] = $tvalue->getValue();
                }
            }
        }

        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = [];
            $childProduct['entity'] = $simpleProduct;
            $child = $this->productRepository->getById($childProduct['entity']);
            $childProduct['call_hide_price'] = $this->getCallHidePriceConfgiruable($checkCallHidePriceParent, $child);
            if ($childProduct['call_hide_price'] == self::CALL_FOR_PRICE) {
                $childProduct['call_hide_price_content'] = '<div id="callforprice_' . $child->getId() .
                    '" class="callforprice-container">
                    <input type="hidden" name="parent_id" value="' . $product->getId() . '">
                    <input type="hidden" name="product" value="' . $child->getId() . '">
                    <input type="hidden" name="product_name" value="' . $child->getName() . '">
                    <a class="callforprice_clickme action primary">'
                    . $this->getCallPriceTextConfigurable($checkCallHidePriceParent, $product, $child) . '</a>
                </div>';
            } elseif ($childProduct['call_hide_price'] == self::HIDE_PRICE) {
                $childProduct['call_hide_price_content'] = '<h2 id="callforprice_text_' . $child->getId() . '"
                class="callforprice_text">' .
                    $this->getCallPriceTextConfigurable($checkCallHidePriceParent, $product, $child) . '</h2>';
            } else {
                $childProduct['call_hide_price_content'] = false;
            }
            $key = '';
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $key .= $child->getData($attrLabel) . '_';
            }
            $result['child'][$key] = $childProduct;
        }
        $result['parent_id'] = $product->getId();
        $result['parent_call_hideprice'] = $checkCallHidePriceParent;
        $result['selector'] = $this->getSelector();
        return $result;
    }

    /**
     * @param string|bool $checkCallHidePriceParent
     * @param mixed $product
     * @return bool|string
     */
    protected function getCallHidePriceConfgiruable($checkCallHidePriceParent, $product)
    {
        if ($checkCallHidePriceParent) {
            return $checkCallHidePriceParent;
        }
        return $this->activeCallHidePrice($product);
    }

    /**
     * @param string|bool $checkCallHidePriceParent
     * @param mixed $parentProduct
     * @param mixed $product
     * @return mixed|string
     */
    protected function getCallPriceTextConfigurable($checkCallHidePriceParent, $parentProduct, $product)
    {
        if ($checkCallHidePriceParent) {
            return $this->getCallforpriceText($parentProduct);
        }
        return $this->getCallforpriceText($product);
    }

    /**
     * @param object $product
     * @param int $customerGroup
     * @return bool|string
     */
    public function isActiveProduct($product, $customerGroup = null)
    {
        $callHidePriceCustomersGroupProduct = $this->filterArray($product->getCallforpriceCustomergroup());
        $callForPriceCustomers = $this->filterArray($this->getCallForPriceCustomers());
        $hidePriceCustomers = $this->filterArray($this->getHidePriceCustomers());
        if ($customerGroup === null) {
            $customerGroup = $this->getCustomerGroupId();
        }
        if ($this->getTypeIsUsedByProduct($product) == self::USE_CONFIG_CALL_PRICE) { // CallForPirce
            return $this->checkGetTypeisUsedByProductOne(
                $callHidePriceCustomersGroupProduct,
                $callForPriceCustomers,
                $customerGroup
            );
        } elseif ($this->getTypeIsUsedByProduct($product) == self::USE_CONFIG_HIDE_PRICE) { // HidePrice
            return $this->checkGetTypeisUsedByProductOneTwo(
                $callHidePriceCustomersGroupProduct,
                $hidePriceCustomers,
                $customerGroup
            );
        } elseif ($this->getTypeIsUsedByProduct($product) == self::USE_CONFIG_DISABLE) { // Disable
            return self::DISABLE_ADVANCEDHIDEPRICE;
        } else { // No select option
            return self::NO_SELECT_OPTION;
        }
    }

    /**
     * @param array $callHidePriceCustomersGroupProduct
     * @param array $callForPriceCustomers
     * @param string $customerGroup
     * @return bool|string
     */
    public function checkGetTypeisUsedByProductOne(
        $callHidePriceCustomersGroupProduct,
        $callForPriceCustomers,
        $customerGroup
    ) {
        if (!empty($callHidePriceCustomersGroupProduct) && count($callHidePriceCustomersGroupProduct) == 1 && $callHidePriceCustomersGroupProduct[0] == -1) {// proudct not set customer group
            if (!empty($callForPriceCustomers) && in_array($customerGroup, $callForPriceCustomers)) {
                return self::CALL_FOR_PRICE;
            } // check global setting
            return false;
        } else {
            if (!empty($callHidePriceCustomersGroupProduct) &&
                in_array($customerGroup, $callHidePriceCustomersGroupProduct)
            ) {
                return self::CALL_FOR_PRICE;
            }
        }
        return false;
    }

    /**
     * @param array $callHidePriceCustomersGroupProduct
     * @param array $hidePriceCustomers
     * @param string $customerGroup
     * @return bool|string
     */
    private function checkGetTypeisUsedByProductOneTwo(
        $callHidePriceCustomersGroupProduct,
        $hidePriceCustomers,
        $customerGroup
    ) {
        if (!empty($callHidePriceCustomersGroupProduct) && count($callHidePriceCustomersGroupProduct) == 1 && $callHidePriceCustomersGroupProduct[0] == -1) {// proudct not set customer group
            if (!empty($hidePriceCustomers) && in_array($customerGroup, $hidePriceCustomers)) {
                return self::HIDE_PRICE;
            } // check global setting
            return false;
        } else {
            if (!empty($callHidePriceCustomersGroupProduct) &&
                in_array($customerGroup, $callHidePriceCustomersGroupProduct)) {
                return self::HIDE_PRICE;
            }
        }
        return false;
    }

    /**
     * @param object $product
     * @param int $storeId
     * @param int $customerGroup
     * @return bool|string
     */
    protected function isActiveGlobal($product, $storeId, $customerGroup =  null)
    {
        $priority = $this->getPriority($storeId);
        $callForPriceCategories = $this->filterArray($this->getCallForPriceCategories());
        $callForPriceCustomers = $this->filterArray($this->getCallForPriceCustomers());
        $hidePriceCustomers = $this->filterArray($this->getHidePriceCustomers());
        $productCategories = array_filter($product->getCategoryIds());
        $hidePriceCategories = $this->filterArray($this->getHidePriceCategories());
        $applyCallForPrice = $this->isNotApllyProduct($product, self::CALL_FOR_PRICE);
        $applyHidePrice = $this->isNotApllyProduct($product, self::HIDE_PRICE);
        if ($customerGroup === null) {
            $customerGroup = $this->getCustomerGroupId();
        }
        $callForPrice = $this->setCallForPrice(
            $productCategories,
            $callForPriceCategories,
            $callForPriceCustomers,
            $customerGroup
        );
        $hidePrice = $this->setHidePrice($productCategories, $hidePriceCategories, $hidePriceCustomers, $customerGroup);
        $checkCallForPrice = $this->callForPrice($priority, $callForPrice, $applyCallForPrice, $hidePrice);
        if ($checkCallForPrice) {
            return $checkCallForPrice;
        } elseif ($priority == self::HIDE_PRICE && $hidePrice && $applyHidePrice
            || $priority == self::CALL_FOR_PRICE && !$callForPrice && $hidePrice && $applyHidePrice
        ) {
            return self::HIDE_PRICE;
        } else {
            return false;
        }
    }

    /**
     * @param array $productCategories
     * @param array $callForPriceCategories
     * @param array $callForPriceCustomers
     * @param string $customerGroup
     * @return bool
     */
    private function setCallForPrice(
        $productCategories,
        $callForPriceCategories,
        $callForPriceCustomers,
        $customerGroup
    ) {
        $callForPrice = (
            !empty(array_intersect($productCategories, $callForPriceCategories)) &&
                !empty($callForPriceCustomers) && in_array($customerGroup, $callForPriceCustomers)
        )
            || (!empty(array_intersect($productCategories, $callForPriceCategories)) && empty($callForPriceCustomers));

        return $callForPrice;
    }

    /**
     * @param array $productCategories
     * @param array $hidePriceCategories
     * @param array $hidePriceCustomers
     * @param string $customerGroup
     * @param array $hidePriceCustomers
     * @return bool
     */
    private function setHidePrice($productCategories, $hidePriceCategories, $hidePriceCustomers, $customerGroup)
    {
        $hidePrice = (!empty(array_intersect($productCategories, $hidePriceCategories)) &&
                !empty($hidePriceCustomers) && in_array($customerGroup, $hidePriceCustomers))
        || (!empty(array_intersect($productCategories, $hidePriceCategories)) && empty($hidePriceCategories));

        return $hidePrice;
    }

    /**
     * @param int $priority
     * @param string $callForPrice
     * @param string $applyCallForPrice
     * @param string $hidePrice
     * @return string
     */
    private function callForPrice($priority, $callForPrice, $applyCallForPrice, $hidePrice)
    {
        if ($priority == self::CALL_FOR_PRICE && $callForPrice && $applyCallForPrice
            || $priority == self::HIDE_PRICE && !$hidePrice && $callForPrice && $applyCallForPrice
        ) {
            return self::CALL_FOR_PRICE;
        }
        return false;
    }

    /**
     * @param object $product
     * @param string $type
     * @return bool
     */
    protected function isNotApllyProduct($product, $type)
    {
        if (self::CALL_FOR_PRICE == $type) {
            $callForPriceNotAplly = $this->filterArray($this->callForPriceNotApply());
            $applyCallForPrice = (!empty($callForPriceNotAplly) &&
                    !in_array($product->getId(), $callForPriceNotAplly)) || empty($callForPriceNotAplly);
            return $applyCallForPrice;
        }

        if (self::HIDE_PRICE == $type) {
            $hidePriceNotAplly = $this->filterArray($this->hidePriceNotApply());
            $applyHidePrice = (!empty($hidePriceNotAplly) &&
                    !in_array($product->getId(), $hidePriceNotAplly)) || empty($hidePriceNotAplly);
            return $applyHidePrice;
        }

        return false;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession->create();
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getExtraField()
    {
        $fields = $this->scopeConfig->getValue(
            'bss_call_for_price/callforprice_design/form_fields',
            ScopeInterface::SCOPE_STORE
        );
        $result = [];
        if ($fields) {
            $result = $this->serializer->unserialize($fields);
            // sort
            $keys = array_column($result, 'field_order');
            array_multisort($keys, SORT_ASC, $result);
        }
        return $result;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStoreObj()->getStore()->getId();
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreObj()
    {
        return $this->storeManagerInterface;
    }

    /**
     * Get call for price text
     * @param int $store
     * @return string
     */
    public function callForPriceText($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CALL_FOR_PRICE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get hide price text
     * @param int $store
     * @return string
     */
    public function callHidePriceText($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HIDE_PRICE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
