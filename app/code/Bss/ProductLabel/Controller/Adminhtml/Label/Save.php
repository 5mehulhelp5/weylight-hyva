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
 * @copyright Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Controller\Adminhtml\Label;

use Bss\ProductLabel\Controller\Adminhtml\Label;
use Bss\ProductLabel\Helper\SaveObject;
use Bss\ProductLabel\Model\LabelFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Save extends Label
{
    /** Label image Directory */
    const IMG_DIR = 'product_label/';

    const ORIGINAL_IMAGE = 'product_label/original_photo/';

    /**
     * @var SaveObject
     */
    protected $saveObjectHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param LabelFactory $labelFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param SaveObject $saveObjectHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        LabelFactory $labelFactory,
        \Psr\Log\LoggerInterface $logger,
        SaveObject $saveObjectHelper,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $labelFactory, $logger);
        $this->saveObjectHelper = $saveObjectHelper;
        $this->json = $json;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $labelModel = $this->labelFactory->create();

            $beforeImageData = null;
            $beforeImage = null;
            $image_data = [];
            $data = $this->getRequest()->getPostValue();
            $data = $this->prepareData($data);

            $labelId = isset($data['label']['id']) ? $data['label']['id'] : null;
            if ($labelId) {
                $labelModel->load($labelId);
                $beforeImageData = $labelModel->getImageData();
                $beforeImage = $labelModel->getImage();
            }
            if (!empty($data['label']['image_data'])) {
                $image_data = $this->json->unserialize($data['label']['image_data']);
            }
            $startDate = $data['label']['valid_start_date'];
            $endDate = $data['label']['valid_end_date'];

            // checking end date > start date
            if ($this->validateTime($startDate, $endDate, $labelModel->getId())) {
                return $this->validateTime($startDate, $endDate, $labelModel->getId());
            }

            $files = $this->getRequest()->getFiles('image');
            $labelModel->loadPost($data['label']);

            if ($this->validateFile($files, $labelModel->getId())) {
                return $this->validateFile($files, $labelModel->getId());
            }

            $resultImage = $this->canUploadFile($files, $image_data);

            if ($resultImage) {
                //delete old image
                $labelImageOld = $labelModel->getImage();
                $this->checkImage($labelImageOld, $resultImage);
                $labelModel->setData('image', $resultImage);
            }

            $labelImg = $labelModel->getImage();
            $this->canResizeImage($files, $beforeImageData, $beforeImage, $labelImg, $data, $image_data);

            try {
                $labelModel->save();
                $specialPrice = $this->checkConditionsSpecialPrice($data);
                return $this->goBack($labelModel->getId(), $specialPrice);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Please review the form and make corrections'));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $labelId]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check conditions has special price
     *
     * @param array $data
     * @return bool
     */
    public function checkConditionsSpecialPrice($data)
    {
        foreach ($data['label']['conditions'] as $item) {
            if (isset($item['attribute'])
                && $item['attribute'] == 'special_price'
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $files
     * @param array $beforeImageData
     * @param string $beforeImage
     * @param string $labelImg
     * @param array $data
     * @param array $imageData
     * @throws \Exception
     */
    protected function canResizeImage($files, $beforeImageData, $beforeImage, $labelImg, $data, $imageData)
    {
        // handle if image position/size changed
        if ((empty($files['name'])) &&
            !empty($imageData) &&
            $beforeImageData !=null &&
            $beforeImage == $labelImg &&
            $data['label']['image_data'] != $beforeImageData
        ) {
            $imgUrl = $this->saveObjectHelper->getFilesystem()->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath() . $labelImg;
            $this->resizeImage($imgUrl, $imageData['widthOrigin'], $imageData['heightOrigin']);
        }
    }

    /**
     * Check Label Image
     *
     * @param string $lableImageOld
     * @param string $resultImage
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function checkImage($lableImageOld, $resultImage)
    {
        if ($lableImageOld && $lableImageOld != $resultImage) {
            $this->deleteImage($lableImageOld);
        }
    }

    /**
     * Prepares specific data
     *
     * @param array $data
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function prepareData($data)
    {
        try {
            //handle convert groups array to string
            if (isset($data['label']['customer_groups'])) {
                $groups_filter = array_filter(array_map('trim', $data['label']['customer_groups']), 'strlen');
                $data['label']['customer_groups'] = implode(',', $groups_filter);
            }
            if (isset($data['label']['store_views'])) {
                $store_filter = array_filter(array_map('trim', $data['label']['store_views']), 'strlen');
                $data['label']['store_views'] = implode(',', $store_filter);
            }

            //handle insert conditions data to label array
            if (isset($data['rule']['conditions'])) {
                $data['label']['conditions'] = $data['rule']['conditions'];
            }
            unset($data['rule']['conditions']);

            // fix for before data of older version
            if (isset($data['parameters']['conditions'])) {
                $data['label']['conditions'] = $data['label']['conditions'] + $data['parameters']['conditions'];
            }

            //handle delete image & set image in case of editing
            if (!empty($data['label']['image']['value'])) {
                if (isset($data['label']['image']['delete'])) {
                    $this->deleteImage($data['label']['image']['value']);
                    $data['label']['image'] = null;
                } elseif (isset($data['label']['image']['value'])) {
                    $data['label']['image'] = $data['label']['image']['value'];
                } else {
                    $data['label']['image'] = null;
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $data;
    }

    /**
     * Handle upload and resize
     *
     * @param int $width
     * @param int $height
     * @param string $fileName
     * @return null|string
     * @throws \Exception
     */
    protected function uploadImage($fileName, $width, $height)
    {
        $imageUrl = null;
        try {
            $uploader = $this->saveObjectHelper->getFileUploaderFactory()->create(['fileId' => 'image']);

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $path = $this->saveObjectHelper->getFilesystem()->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath(self::ORIGINAL_IMAGE);
            // Avoid overwriting when having the same file name
            $result = $uploader->save($path, time() . '.' . $uploader->getFileExtension());
            $imageUrl = self::ORIGINAL_IMAGE . $result['file'];
            $absoluteImageUrl = $path . $result['file'];

            $this->resizeImage($absoluteImageUrl, $width, $height);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Image can\'t be uploaded'));
            $this->logger->critical($e);
        }

        return $imageUrl;
    }

    /**
     * Handle resize image
     * @param $absoluteImageUrl
     * @param $width
     * @param $height
     * @throws \Exception
     */
    protected function resizeImage($absoluteImageUrl, $width = 250, $height = null)
    {
        $imageResize = $this->saveObjectHelper->getImageFactory()->create();
        try {
            $imageResize->open($absoluteImageUrl);
            $imageResize->constrainOnly(false);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize((int) $width,(int) $height);
            if (!empty($absoluteImageUrl)) {
                if (strpos($absoluteImageUrl, self::ORIGINAL_IMAGE) !== false) {
                    $absoluteImageUrl = str_replace(self::ORIGINAL_IMAGE, self::IMG_DIR, $absoluteImageUrl);
                }
                $imageResize->save($absoluteImageUrl);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Handle delete image
     *
     * @param $image
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function deleteImage($image)
    {
        if ($image == null) {
            return;
        }
        $resizeImage = str_replace(self::ORIGINAL_IMAGE, self::IMG_DIR, $image);
        $uploadDir = $this->saveObjectHelper->getFilesystem()
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        if ($this->saveObjectHelper->getFile()->isExists($uploadDir . $image)) {
            try {
                $this->saveObjectHelper->getFile()->deleteFile($uploadDir . $image);
                $this->saveObjectHelper->getFile()->deleteFile($uploadDir . $resizeImage);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Image can\'t be deleted'));
                $this->logger->critical($e);
            }
        }
    }

    /**
     * @param string $start
     * @param string $end
     * @param int $labelId
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    protected function validateTime($start, $end, $labelId)
    {
        try {
            if (!empty($start) && !empty($end)) {
                $st = $this->saveObjectHelper->getDate()->date('Y-m-d H:i:s', $start);
                $et = $this->saveObjectHelper->getDate()->date('Y-m-d H:i:s', $end);
                if ($st >= $et) {
                    $this->messageManager->addErrorMessage(__('Valid End Date must follow Start Date.'));
                    return $this->returnResult('*/*/edit', ['id' => $labelId, '_current' => true], ['error' => true]);
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Please input valid date'));
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * @param array $files
     * @param int $labelId
     * @param $image_data
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json|null|string
     * @throws \Exception
     */
    protected function validateFile($files, $labelId)
    {
        if (!empty($files['name'])) {
            if (empty($files['tmp_name']) || $files['size'] == 0) {
                $this->messageManager->addErrorMessage(__('That image can not upload, please choose another'));
                return $this->returnResult('*/*/edit', ['id' => $labelId, '_current' => true], ['error' => true]);
            }
        }
        return false;
    }

    /**
     * @param array $files
     * @param array $imageData
     * @return bool|null|string
     * @throws \Exception
     */
    protected function canUploadFile($files, $imageData)
    {
        if (!empty($files['name']) && !empty($imageData)) {
            $resultImage = $this->uploadImage($files['name'], $imageData['widthOrigin'], $imageData['heightOrigin']);
            return $resultImage;
        }

        return false;
    }
    /**
     * @param bool $specialPrice
     * @param int $labelId
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    protected function goBack($labelId, $specialPrice)
    {
        if ($specialPrice) {
            $this->messageManager->addNoticeMessage(
                __('Note : Only applied to products with special price that has specific date range. You can set date range in Product Edit Page -> Pricing -> Advanced Pricing.')
            );
        }
        $this->messageManager->addSuccessMessage(__('The item has been saved.'));
        if ($this->getRequest()->getParam('back', false)) {
            // Display success message
            return $this->returnResult('*/*/edit', ['id' => $labelId, '_current' => true], ['error' => false]);
        }
        // Go to grid page
        return $this->returnResult('*/*/', [], ['error' => false]);
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $response
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->saveObjectHelper->getLayoutFactory()->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }

    /**
     * @return bool
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }
}
