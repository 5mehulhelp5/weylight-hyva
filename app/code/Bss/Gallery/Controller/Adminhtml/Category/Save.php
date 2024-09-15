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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Controller\Adminhtml\Category;

use Magento\Framework\App\Cache\TypeListInterface as CacheTypeListInterface;

/**
 * Class Save
 *
 * @package Bss\Gallery\Controller\Adminhtml\Category
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var CacheTypeListInterface
     */
    protected $cache;

    /**
     * @param Action\Context $context
     */
    protected $uploaderFactory;

    /**
     * @var \Bss\Gallery\Model\Category\Image
     */
    protected $imageModel;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    protected $bssCategoryFactory;

    /**
     * @var \Bss\Gallery\Model\ItemFactory
     */
    protected $bssItemFactory;

    /**
     * @var \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Bss\Gallery\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Bss\Gallery\Model\Category\Image $imageModel
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory
     * @param \Bss\Gallery\Model\ItemFactory $bssItemFactory
     * @param \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $collectionFactory
     * @param \Bss\Gallery\Helper\Data $dataHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param CacheTypeListInterface $cache
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Bss\Gallery\Model\Category\Image $imageModel,
        \Magento\Backend\Helper\Js $jsHelper,
        \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory,
        \Bss\Gallery\Model\ItemFactory $bssItemFactory,
        \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        \Bss\Gallery\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger,
        CacheTypeListInterface $cache
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageModel = $imageModel;
        $this->jsHelper = $jsHelper;
        $this->bssCategoryFactory = $bssCategoryFactory;
        $this->bssItemFactory = $bssItemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Bss\Gallery\Model\Category $model */
            $model = $this->bssCategoryFactory->create();
            $itemIds = $this->setItemId($model);
            $categoryImage = $this->getRequest()->getPostValue('category_image');
            if (isset($categoryImage)) {
                $itemGridSerializedInputData = $this->jsHelper->decodeGridSerializedInput($categoryImage);
                $itemIds = [];
                foreach (array_keys($itemGridSerializedInputData) as $key) {
                    $itemIds[] = $key;
                }
                $itemIds = implode(',', $itemIds);
            }
            $storeIds = implode(',', $data['store_ids']);
            $model->setData($data);
            $model->setData('Item_ids', $itemIds);
            $model->setData('store_ids', $storeIds);
            $this->_eventManager->dispatch(
                'gallery_category_prepare_save',
                ['category' => $model, 'request' => $this->getRequest()]
            );

            try {
                if (isset($data['item_thumb_id'])) {
                    $categoryThumb = $data['item_thumb_id'];
                    $itemModel = $this->bssItemFactory->create();
                    $item = $itemModel->load($categoryThumb);
                    $itemImage = $item->getImage();
                    $model->setThumbnail($itemImage);
                    $model->setItemThumbId($categoryThumb);
                } else {
                    $model->setThumbnail('');
                    $model->setItemThumbId('');
                }

                $title = $data['title'];
                $metaKeywords = $data['category_meta_keywords'];
                $metaDescription = $data['category_meta_description'];
                $model->setCategoryMetaKeywords($metaKeywords);
                $model->setMetaDescription($metaDescription);
                //set url
                $url = $this->setUrls($title, $model);
                $model->setUrlKey($url);
                $model->save();

                //set category_ids for item
                $category = $model;
                $id = $category->getId();
                $itemIds = explode(',', $category->getData('Item_ids') ?? '');
                $allItems = $this->collectionFactory->create();
                $this->foreachAllItem($allItems, $itemIds, $id);
                $this->messageManager->addSuccessMessage(__('You saved this Album.'));
                $this->cache->invalidate('full_page');
                $this->_session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addExceptionMessage($e, __('Something went wrong, please try again !'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['category_id' => $this->getRequest()->getParam('category_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Foreach all item
     *
     * @param \Bss\Gallery\Model\ResourceModel\Item\Collection $allItems
     * @param array $itemIds
     * @param int $id
     */
    private function foreachAllItem($allItems, $itemIds, $id)
    {
        foreach ($allItems as $item) {
            $itemId = $item->getId();
            $cateIds = explode(',', $item->getData('category_ids') ?? '');
            if (in_array($itemId, $itemIds) && !in_array($id, $cateIds)) {
                array_push($cateIds, $id);
                $cateIds = ltrim(rtrim(implode(',', $cateIds), ","), ",");
                $item->setData('category_ids', $cateIds);
                $this->saveItem($item);
            } else {
                if (in_array($id, $cateIds)) {
                    $key = array_search($id, $cateIds);
                    unset($cateIds[$key]);
                    $item->setData('category_ids', implode(',', $cateIds));
                    $this->saveItem($item);
                }
            }
        }
    }

    /**
     * Set item identifier
     *
     * @param \Bss\Gallery\Model\Category $model
     * @return null|string
     */
    private function setItemId($model)
    {
        $id = $this->getRequest()->getParam('category_id');
        if ($id) {
            $model->load($id);
            return $model->getData('Item_ids');
        } else {
            return null;
        }
    }

    /**
     * Set urls
     *
     * @param string $title
     * @param mixed $model
     * @return string
     */
    private function setUrls($title, $model)
    {
        $url = $this->dataHelper->formatUrlKey($title);
        if ($model->checkUrlKey($url, $model->getId()) != null) {
            $url .= '-' . $this->dataHelper->randomStr();
        }
        return $url;
    }

    /**
     * Save the item
     *
     * @param mixed $item
     * @return mixed
     * @throws \Exception
     */
    private function saveItem($item)
    {
        try {
            return $item->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this;
        }
    }

    /**
     * If is allow to save
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::category_save');
    }
}
