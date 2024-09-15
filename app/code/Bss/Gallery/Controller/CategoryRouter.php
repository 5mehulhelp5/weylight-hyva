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
namespace Bss\Gallery\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Class CategoryRouter
 *
 * @package Bss\Gallery\Controller
 * @codingStandardsIgnoreFile
 */
class CategoryRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Bss\Gallery\Helper\Category
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * CategoryRouter constructor.
     *
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Bss\Gallery\Model\CategoryFactory $categoryFactory
     * @param \Bss\Gallery\Helper\Category $categoryHelper
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Bss\Gallery\Model\CategoryFactory $categoryFactory,
        \Bss\Gallery\Helper\Category $categoryHelper,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->actionFactory = $actionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryHelper = $categoryHelper;
        $this->file = $file;
    }

    /**
     * Match corresponding URL Rewrite and modify request.
     *
     * @param \Magento\Framework\App\RequestInterface|HttpRequest $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pathInfo = $this->file->getPathInfo($request->getPathInfo());
        $urlKey = $pathInfo['basename'];
        $category = $this->categoryFactory->create();
        $categoryId = $category->checkUrlKey($urlKey);
        if (!$categoryId) {
            return null;
        }
        if (!$this->categoryHelper->isEnabledInFrontend()) {
            return null;
        }
        $request->setModuleName('gallery')->setControllerName('cateview')->setActionName('index');
        $request->setParam('category_id', $categoryId);
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }
}
