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
 * @package    Bss_SeoAltText
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);
namespace Bss\SeoAltText\Controller\Adminhtml\Album;

use Bss\SeoAltText\Helper\Data;
use Bss\SeoAltText\Model\ResourceModel\ProductAlbum;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class GenerateAltTag extends \Magento\Backend\App\Action
{
    public const PAGE_SIZE_PRODUCT = 300;

    /**
     * @var \Bss\SeoAltText\Model\ResourceModel\ProductAlbum
     */
    protected $productAlbum;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Bss\SeoAltText\Helper\Data
     */
    protected $bssHelperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Construct.
     *
     * @param Context $context
     * @param ProductAlbum $productAlbum
     * @param Attribute $eavAttribute
     * @param Data $bssHelperData
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        Context $context,
        \Bss\SeoAltText\Model\ResourceModel\ProductAlbum $productAlbum,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Bss\SeoAltText\Helper\Data $bssHelperData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->productAlbum = $productAlbum;
        $this->eavAttribute = $eavAttribute;
        $this->bssHelperData = $bssHelperData;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute generate all alt tag.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $result = $this->beforeUpdateAltTag();

            if ($result) {
                $this->messageManager->addSuccessMessage(__("You have successfully changed all the alt tag."));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }

    /**
     * Before get product collection.
     *
     * @return bool
     */
    public function beforeUpdateAltTag()
    {
        $altTemplate = $this->bssHelperData->getAltTemplate();

        if ($altTemplate) {
            preg_match_all("/\[product_(.*?)\]/", $altTemplate, $attr);
            $attrFilter = end($attr);

            $collection = $this->productCollectionFactory->create()->setPageSize(self::PAGE_SIZE_PRODUCT);
            $lastPage = $collection->getLastPageNumber();

            try {
                $this->updateAltTag($lastPage, $altTemplate, $attrFilter);
                return true;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                return false;
            }
        } else {
            $this->messageManager->addWarningMessage(__('Please save config "Alt Tag Template" before Generate!'));
            return false;
        }
    }

    /**
     * Get data new alt tag.
     *
     * @param int $lastPage
     * @param string $altTemplate
     * @param array $attrFilter
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateAltTag($lastPage, $altTemplate, $attrFilter)
    {
        $dataChange = [];
        $page = 0;

        $attributeAlt['image_label'] = $this->eavAttribute->getIdByCode('catalog_product', 'image_label');
        $attributeAlt['small_image_label'] = $this->eavAttribute->getIdByCode('catalog_product', 'small_image_label');
        $attributeAlt['thumbnail_label'] = $this->eavAttribute->getIdByCode('catalog_product', 'thumbnail_label');

        while (true) {
            ++$page;

            $productCollection = $this->paginateCollection($page, self::PAGE_SIZE_PRODUCT, $attrFilter);

            foreach ($productCollection as $product) {
                $newAlt = $this->bssHelperData->convertVar($product, $altTemplate);
                $dataChange[$product->getId()] = $newAlt;
            }

            if ($page >= $lastPage) {
                break;
            }
        }

        $this->productAlbum->saveMultipleData($dataChange, $attributeAlt);
    }

    /**
     * Set page and page size to collection.
     *
     * @param int $page
     * @param int $pageSize
     * @param array $attrFilter
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function paginateCollection($page, $pageSize, $attrFilter)
    {
        $productCollection = $this->productCollectionFactory->create();
        return $productCollection->addAttributeToSelect($attrFilter)->setPage($page, $pageSize);
    }
}
