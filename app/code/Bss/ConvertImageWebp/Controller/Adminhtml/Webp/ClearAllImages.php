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
 *  @category  BSS
 *  @package   Bss_ConvertImageWebp
 *  @author    Extension Team
 *  @copyright Copyright (c) 2021-2022 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConvertImageWebp\Controller\Adminhtml\Webp;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class ClearAllImages extends \Magento\Backend\App\Action
{
    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var WriteInterface
     */
    protected $deleteDirectory;

    /**
     * ClearAllImages constructor.
     *
     * @param Filesystem $fileSystem
     * @param Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Filesystem $fileSystem,
        Context $context
    ) {
        parent::__construct($context);
        $this->directory = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Delete folder bss/webp
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $this->directory->delete("bss/webp");
        $this->messageManager->addSuccessMessage(__("You cleared all images webp"));
        $this->_redirect(
            'adminhtml/system_config/edit',
            ["section" => "convert_image_webp"]
        );
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_ConvertImageWebp::delete_all_images');
    }
}
