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
namespace Bss\Gallery\Controller\Adminhtml\Item;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Magento\Backend\App\Action;

/**
 * Class Save
 *
 * @package Bss\Gallery\Controller\Adminhtml\Item
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     */
    protected $uploaderFactory;

    /**
     * @var \Bss\Gallery\Model\Item\Image
     */
    protected $imageModel;

    /**
     * @var \Bss\Gallery\Model\ItemFactory
     */
    protected $bssItemFactory;

    /**
     * @var \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Backend\Model\Sessionview
     */
    protected $session;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cache;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Bss\Gallery\Model\Item\Image $imageModel
     * @param \Bss\Gallery\Model\ItemFactory $bssItemFactory
     * @param \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Cache\TypeListInterface $cache
     */
    public function __construct(
        Action\Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Bss\Gallery\Model\Item\Image $imageModel,
        \Bss\Gallery\Model\ItemFactory $bssItemFactory,
        \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Cache\TypeListInterface $cache
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageModel = $imageModel;
        $this->bssItemFactory = $bssItemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->session = $context->getSession();
        $this->cache = $cache;
    }

    /**
     * Is user allowed to save item
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::item_save');
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
            /** @var \Bss\Gallery\Model\Item $model */
            $model = $this->bssItemFactory->create();

            $id = $this->getRequest()->getParam('item_id');
            if ($id) {
                $model->load($id);
            }
            if (is_array($this->getRequest()->getParam('category_ids'))) {
                $category_ids = implode(',', $this->getRequest()->getParam('category_ids'));
                $data['category_ids'] = $category_ids;
            } else {
                $category_ids = '';
                $data['category_ids'] = $category_ids;
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'gallery_item_prepare_save',
                ['item' => $model, 'request' => $this->getRequest()]
            );

            try {
                $imageName = $this->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
                if ($imageName === false) {
                    $this->session->setFormData(false);
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
                }
                $model->setImage($imageName);

                $sort = (int)$data['sorting'];
                //set sortOrder
                $this->checkImage($sort, $model);
                $model->save();
                //save items id in category
                $cateIds = explode(',', $model->getCategoryIds() ?? '');
                $id = $model->getId();
                $allCategories = $this->collectionFactory->create();

                $this->foreachAllCategorie($allCategories, $cateIds, $id);

                $this->messageManager->addSuccessMessage(__('You saved this Item.'));
                $this->cache->invalidate('full_page');
                $this->session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the item.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['item_id' => $this->getRequest()->getParam('item_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check image
     *
     * @param string|null $sort
     * @param mixed $model
     */
    private function checkImage($sort, $model)
    {
        if ($sort != '') {
            $model->setData('sorting', $sort);
        } else {
            $model->setData('sorting', 10);
        }
    }

    /**
     * Foreach all categories
     *
     * @param \Bss\Gallery\Model\ResourceModel\Category\Collection $allCategories
     * @param array $cateIds
     * @param int $id
     * @throws \Exception
     */
    private function foreachAllCategorie($allCategories, $cateIds, $id)
    {
        foreach ($allCategories as $cate) {
            $_catId = $cate->getId();
            $itemIds = explode(',', $cate->getData('Item_ids') ?? '');
            if (in_array($_catId, $cateIds)) {
                if (!in_array($id, $itemIds)) {
                    array_push($itemIds, $id);
                    $itemIds = ltrim(rtrim(implode(',', $itemIds), ","), ",");
                    $cate->setData('Item_ids', $itemIds);
                    $this->saveCate($cate);
                }
            } else {
                if (in_array($id, $itemIds)) {
                    $key = array_search($id, $itemIds);
                    unset($itemIds[$key]);
                    $cate->setData('Item_ids', implode(',', $itemIds));
                    $this->saveCate($cate);
                }
            }
        }
    }

    /**
     * Get name of uploaded file
     *
     * @param string $input
     * @param string $destinationFolder
     * @param array $data
     * @return string
     */
    protected function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                $check = substr($result['file'], strrpos($result['file'], '.'));
                $allowType = ['.jpg', '.jpeg', '.png', '.bmp', '.gif'];
                $mess = 'Please select valid file type image. The supported file types are .jpg , .png , .gif, .bmp';
                if (!in_array($check, $allowType)) {
                    $this->messageManager->addErrorMessage(__($mess));
                    return false;
                }
                return $result['file'];
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            if ($e->getCode() == \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }

    /**
     * Save the category
     *
     * @param \Bss\Gallery\Model\Category $cate
     * @return \Bss\Gallery\Model\Category
     * @throws \Exception
     */
    private function saveCate($cate)
    {
        try {
            return $cate->save();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this;
        }
    }
}
