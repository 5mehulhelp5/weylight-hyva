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
 * @package    Bss_ChatGPT
 * @author     Extension Team
 * @copyright  Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ChatGPT\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ChatGPT extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Bss_ChatGPT::system-call-chatgpt.phtml';

    /**
     * @var \Bss\ChatGPT\Model\ChatGPT
     */
    protected $chatGPT;

    /**
     * Construct.
     *
     * @param \Bss\ChatGPT\Model\ChatGPT $chatGPT
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        \Bss\ChatGPT\Model\ChatGPT $chatGPT,
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->chatGPT = $chatGPT;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Get element html.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml($element)
    {
        return $this->_toHtml();
    }

    /**
     * Remove scope.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render($element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get url call API
     *
     * @return string
     */
    public function getUrlApi()
    {
        return $this->chatGPT->getUrlChatGPT();
    }
}
