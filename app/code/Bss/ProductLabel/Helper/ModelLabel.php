<?php
/**
 *
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
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Helper;

class ModelLabel extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $condCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $conditionProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $action;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $iterator;

    /**
     * ModelLabel constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $helper
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionProduct
     * @param \Magento\Framework\Model\ResourceModel\Iterator $iterator
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $action
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Data $helper,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionProduct,
        \Magento\Framework\Model\ResourceModel\Iterator $iterator,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->condCombineFactory = $condCombineFactory;
        $this->conditionProduct = $conditionProduct;
        $this->iterator = $iterator;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    public function getProductAction()
    {
        return $this->action;
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    public function getCondCombineFactory()
    {
        return $this->condCombineFactory;
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    public function getConditionProduct()
    {
        return $this->conditionProduct;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getProductCollectionFactory()
    {
        return $this->collectionFactory;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getProductFactory()
    {
        return $this->productFactory;
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return $this->helper->getBatchSize();
    }
}
