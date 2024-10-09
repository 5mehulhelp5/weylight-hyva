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
namespace Bss\AdvancedHidePrice\Block\Adminhtml\Request\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Json\Helper\Data;

class SendMail extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var string
     */
    protected $_template = 'sendmail.phtml';

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * SendMail constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Pricing\Helper\Data $productHelper
     * @param Data $jsonHelper
     * @param FormKey $formKey
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $productHelper,
        Data $jsonHelper,
        FormKey $formKey,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->formKey = $formKey;
        $this->jsonHelper = $jsonHelper;
        $this->serializer = $serializer;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the item grid.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sendMail');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * @return \Bss\AdvancedHidePrice\Model\Request
     */
    public function getFormRequest()
    {
        /** @var \Bss\AdvancedHidePrice\Model\Request $model */
        $model = $this->coreRegistry->registry('callforprice_request');
        return $model;
    }

    /**
     * @return string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductInfo()
    {
        $id = $this->getFormRequest()->getProductIds();
        $product = $this->getProduct();
        if ($product && $product->getId()) {
            $productInfo = __('Name') . ' : ' . '<a target="_blank" href="'
                . $this->getUrl('catalog/product/edit/', ['id' => $id]) . '">'
                . $product->getName() . '</a>' . ' - ' . __('Sku') . ' : ' . $product->getSku() . '<br/>';
            $productInfo .= __('Product Price') . ' : '
                . $this->productHelper->currency($product->getFinalPrice(), true, false) . '<br/>';
            if ($this->getCustomOptionData()) {
                $price = 0;
                foreach ($this->getCustomOptionData() as $item) {
                    $price += $item['price'];
                }
                $productInfo .= __('Custom Option Price') . ' : '
                    . $this->productHelper->currency($price, true, false) . '<br/>';
            }
            return $productInfo;
        } else {
            return;
        }
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        $id = $this->getFormRequest()->getProductIds();
        try {
            $product = $this->productRepository->getById($id);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = '';
        }
        return $product;
    }

    /**
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getFormRequest()->getCustomerEmail();
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('callforprice/request/sendmailprocess');
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return mixed
     */
    public function getCustomOption()
    {
        $request = $this->getFormRequest();
        return $request->getRequiredOptions();
    }

    /**
     * @return mixed
     */
    protected function getCustomOptionData()
    {
        $request = $this->getFormRequest();
        $customOption = $request->getRequiredOptions();
        if ($customOption) {
            return $this->serializer->unserialize($customOption);
        }
        return false;
    }
}
