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

namespace Bss\AdvancedHidePrice\Controller\Adminhtml\Request;

use Magento\Framework\Json\Helper\Data;

class SendMailProcess extends \Magento\Framework\App\Action\Action
{
    /**
     * @var
     */
    protected $resultLayoutFactory;
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Bss\AdvancedHidePrice\Model\Request
     */
    protected $model;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Bss\AdvancedHidePrice\Helper\GetType
     */
    protected $getType;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */

    /**
     * @var Data
     */
    protected $serializer;

    /**
     * SendMailProcess constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Framework\Pricing\Helper\Data $productHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Escaper $escaper
     * @param \Bss\AdvancedHidePrice\Model\Request $model
     * @param \Bss\AdvancedHidePrice\Helper\GetType $getType
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $productHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Escaper $escaper,
        \Bss\AdvancedHidePrice\Model\Request $model,
        \Bss\AdvancedHidePrice\Helper\GetType $getType,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->context = $context;
        parent::__construct($context);
        $this->getType = $getType;
        $this->messageManager = $context->getMessageManager();
        $this->helper = $helper;
        $this->productHelper = $productHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->model = $model;
        $this->transportBuilder = $transportBuilder;
        $this->layout = $layout;
        $this->serializer = $serializer;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute()
    {
        $customerEmail = $this->getRequest()->getParam('customer_email');
        $productPrice = $this->productHelper->currency($this->getRequest()->getParam('product_price'), true, false);
        $comment = $this->getRequest()->getParam('comment');
        $productName = $this->getRequest()->getParam('product_name');
        $senderEmail = $this->helper->getEmailSender();
        $senderName = $this->helper->getEmailSenderName();
        $customOption = $this->getRequest()->getParam('custom_option');
        $model = $this->model;
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This request no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            } else {
                $customerName = $model->getCustomerName();
                $addtional = $this->helper->returnSerializer()->unserialize($model->getContent());
                $additionalHtml = "";
                //add custom option to email
                $option = $this->addProductOption($customOption, $model);
                foreach ($addtional as $key => $value) {
                    $label = explode('__BssAdvancedHidePrice__', $key)[0];
                    $type = explode('__BssAdvancedHidePrice__', $key)[2];
                    $additionalHtml .= $this->getType->fieldToHtmlEmail($label, $type, $value);
                }
                $date = $model->getCreatedAt();
                $this->inlineTranslation->suspend();
                try {
                    $sender = [
                        'name' => $this->escaper->escapeHtml($senderName),
                        'email' => $this->escaper->escapeHtml($senderEmail),
                    ];
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier($this->helper->getAdminResponseEmailTemplate())
                        ->setTemplateOptions(
                            [
                                'area' => $this->getType->getAreaFrontend(),
                                'store' => $this->getType->getStoreManager()->getStore()->getId(),
                            ]
                        )
                        ->setTemplateVars([
                            'customer_name' => $customerName,
                            'product_price' => $productPrice,
                            'product_name' => $productName,
                            'custom_option' => $option,
                            'date' => $date,
                            'comment' => $comment,
                            'additional_field' => $additionalHtml
                        ])->setFrom($sender)
                        ->addTo($customerEmail)
                        ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                    $model->setEmailSent(1);
                    $model->save();
                    $this->messageManager->addSuccessMessage(
                        __('A Email Was Sent To Customer.')
                    );
                    $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong, please try again !'));
                    $this->inlineTranslation->resume();
                    $this->getType->getLogger()->debug($e);
                    $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }
            }
        }
    }

    /**
     * @param mixed $customOption
     * @param mixed $model
     * @return string
     */
    protected function addProductOption($customOption, $model)
    {
        if ($customOption) {
            $customOption = $this->serializer->unserialize($customOption);
            $option = $this->layout
                ->createBlock(\Magento\Framework\View\Element\Template::class)
                ->setTemplate('Bss_AdvancedHidePrice::customoption.phtml')
                ->setCustomOption($customOption)
                ->setCustomerEmail($model->getCustomerEmail())
                ->setProductId($model->getProductIds())
                ->setMediaPath($this->helper->getStoreObj()
                    ->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                )
                ->toHtml();
        } else {
            $option = '';
        }
        return $option;
    }

    /**
     * Is the user allowed to view the callforprice request.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdvancedHidePrice::request_access');
    }
}
