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
namespace Bss\AdvancedHidePrice\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterSave implements ObserverInterface
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Bss\AdvancedHidePrice\Helper\GetType
     */
    protected $getType;

    /**
     * AfterSave constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Bss\AdvancedHidePrice\Helper\GetType $getType
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Bss\AdvancedHidePrice\Helper\GetType $getType,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->getType = $getType;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
        $this->_escaper = $escaper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\MailException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getFormdata();
        $customerEmail = $request->getCustomerEmail();
        $customerName = $request->getCustomerName();
        $senderEmail = $this->helper->getEmailSender();
        $senderName = $this->helper->getEmailSenderName();
        $adminEmailRecipient = $this->helper->getAdminEmailNotify();
        $requestId = $request->getId();
        $product_id = $request->getProductIds();
        $product_name = $request->getProductName();

        if (!$customerEmail || !$adminEmailRecipient) {
            return;
        }
        $this->getType->getInlineTranslation()->suspend();
        try {
            $addtional = $this->helper->returnSerializer()->unserialize($request->getContent());
            $additionalHtml = "";
            foreach ($addtional as $key => $value) {
                $label = explode('__BssAdvancedHidePrice__', $key)[0];
                $type = explode('__BssAdvancedHidePrice__', $key)[2];
                $additionalHtml .= $this->getType->fieldToHtmlEmail($label, $type, $value);
            }
            $sender = [
                'name' => $this->_escaper->escapeHtml($senderName),
                'email' => $this->_escaper->escapeHtml($senderEmail),
            ];
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->helper->getAdminNotifyEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars([
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'id' => $requestId,
                    'product_id' => $product_id,
                    'additional_field' => $additionalHtml,
                    'product_name' => $product_name
                ])
                ->setFrom($sender)
                ->addTo($adminEmailRecipient)
                ->getTransport();
            $transport->sendMessage();
            $this->getType->getInlineTranslation()->resume();
            return;
        } catch (\Exception $e) {
            $this->getType->getInlineTranslation()->resume();
            $this->getType->getLogger()->debug($e);
            return;
        }
    }
}
