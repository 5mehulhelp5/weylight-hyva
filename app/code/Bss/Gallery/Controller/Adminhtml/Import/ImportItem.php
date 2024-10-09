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
namespace Bss\Gallery\Controller\Adminhtml\Import;

/**
 * Class ImportItem
 *
 * @package Bss\Gallery\Controller\Adminhtml\Import
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ImportItem extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\Gallery\Model\ResourceModel\ItemImport
     */
    protected $importModel;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ImportItem constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\Gallery\Model\ResourceModel\ItemImport $importModel
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\Gallery\Model\ResourceModel\ItemImport $importModel,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\File\Size $fileSize,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
        $this->request=$request;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSize = $fileSize;
        $this->logger = $logger;
    }

    /**
     * Execute import item
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->varDirectory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $filepath = "import/image-item/" . $this->request->getFiles('file-item')['name'];
        $size = $this->request->getFiles('file-item')['size'];

        if (($size==0) || ($size>$this->fileSize->getMaxFileSize())) {
            $this->messageManager->addErrorMessage($this->getMaxUploadSizeMessage());
            $this->resultRedirectFactory->create()->setPath(
                '*/*/index',
                ['_secure'=>$this->getRequest()->isSecure()]
            );
        }
        try {
            $target = $this->varDirectory->getAbsolutePath('import/image-item');
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'file-item']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(false);
            $result = $uploader->save($target);
            if ($result['file']) {
                $this->messageManager->addSuccessMessage(
                    __('File has been successfully uploaded in var/import/image-item')
                );
            }
            $this->importModel->setFilePath($filepath);
            $this->importModel->importFromCsvFile();
            $this->messageManager->addSuccessMessage(__('Inserted Row(s): %1', $this->importModel->getInsertedRows()));

            if ($this->importModel->getInvalidRows()>0) {
                $errorMessage = __('Invalid Row(s): %1', $this->importModel->getInvalidRows());

                if ($this->importModel->getWrongTitleRows() != "") {
                    $errorMessage .= "<br>" . __('Empty item title in row(s): %1', $this->importModel->getWrongTitleRows());
                }

                if ($this->importModel->getWrongDescriptionRows() != "") {
                    $errorMessage .= "<br>" . __('Empty item description in row(s): %1', $this->importModel->getWrongDescriptionRows());
                }
                if ($this->importModel->getWrongImagePathRows() != "") {
                    $errorMessage .= "<br>". __('Empty image path or Invalid file type image item in row(s): %1', $this->importModel->getWrongImagePathRows());
                }
                if ($this->importModel->getWrongSortOrderRows() != "") {
                    $errorMessage .= "<br>" . __('Invalid sort order value in row(s): %1', $this->importModel->getWrongSortOrderRows());
                }
                $this->messageManager->addErrorMessage(__($errorMessage));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    /**
     * Get message of max upload size
     *
     * @return \Magento\Framework\Phrase|string
     */
    protected function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->fileSize->getMaxFileSizeInMb();
        if ($maxImageSize) {
            $message = __('Make sure your file isn\'t more than %1M.', $maxImageSize);
        } else {
            $message = __('We can\'t provide the upload settings right now.');
        }
        return $message;
    }

    /**
     * Is the user allowed to view the gallery category grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::item_import');
    }
}
