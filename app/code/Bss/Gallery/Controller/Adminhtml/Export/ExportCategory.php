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
namespace Bss\Gallery\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;

/**
 * Class ExportCategory
 *
 * @package Bss\Gallery\Controller\Adminhtml\Export
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExportCategory extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\Gallery\Model\ResourceModel\Export
     */
    protected $export;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $io;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ExportCategory constructor.
     *
     * @param Context $context
     * @param \Bss\Gallery\Model\ResourceModel\Export $export
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Filesystem\Io\File $io
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Bss\Gallery\Model\ResourceModel\Export $export,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->export = $export;
        $this->filesystem = $filesystem;
        $this->datetime = $datetime;
        $this->io = $io;
        $this->csv = $csv;
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute export category
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->varDirectory = $this->filesystem
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $dir = $this->varDirectory->getAbsolutePath('export/gallery');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if ($this->getRequest()->getParam('export_file_type') == "CSV") {
                $currentDate = $this->export->formatDate($this->datetime->date());
                $outputFile = $dir . "/Album_" . $currentDate . ".csv";
                $fileName = "Album_" . $currentDate . ".csv";
                $this->io->checkAndCreateFolder($dir);

                $albums = $this->export->getAlbumTable();
                $data = $this->export->getExportAlbums($albums);
                $this->csv->saveData($outputFile, $data);

                $this->fileFactory->create(
                    $fileName,
                    [
                        'type'  => "filename",
                        'value' => "export/gallery/" . $fileName,
                        'rm'    => true,
                    ],
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'text/csv',
                    null
                );
                $resultRaw = $this->resultRawFactory->create();
                return $resultRaw;
            }
            $this->messageManager->addSuccessMessage(__('File type does not allowed. Please try again.'));
            return $resultRedirect->setPath('gallery/import/index');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    /**
     * Is the user allowed to view the gallery category grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_Gallery::category_export');
    }
}
