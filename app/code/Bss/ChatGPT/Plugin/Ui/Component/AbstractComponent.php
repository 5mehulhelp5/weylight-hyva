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
namespace Bss\ChatGPT\Plugin\Ui\Component;

use Magento\Framework\Exception\LocalizedException;

class AbstractComponent
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Bss\ChatGPT\Model\Config
     */
    protected $config;

    /**
     * @var \Bss\ChatGPT\Model\ChatGPT
     */
    protected $chatGPT;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct.
     *
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Bss\ChatGPT\Model\Config $config
     * @param \Bss\ChatGPT\Model\ChatGPT $chatGPT
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Bss\ChatGPT\Model\Config $config,
        \Bss\ChatGPT\Model\ChatGPT $chatGPT,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->formKey = $formKey;
        $this->config = $config;
        $this->chatGPT = $chatGPT;
        $this->storeManager = $storeManager;
    }

    /**
     * Add data in html-code.js
     *
     * @param \Magento\Ui\Component\AbstractComponent $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJsConfig(\Magento\Ui\Component\AbstractComponent $subject, $result)
    {
        if (!$this->config->isEnable()) {
            return $result;
        }

        if (isset($result['extends']) && $result['extends'] === 'pagebuilder_html_form') {
            $result['url_api'] = $this->chatGPT->getUrlChatGPT();
            $result['form_key'] = $this->formKey->getFormKey();

            $page = ['product', 'category', 'cms'];
            $stores = $this->storeManager->getStores();
            foreach ($page as $request) {
                foreach ($stores as $store) {
                    $result['message_default'][$request][$store['store_id']]['system_role']
                        = $this->config->getDefaultSystemRole($request, $store['store_id']);
                    $result['message_default'][$request][$store['store_id']]['prompt']
                        = $this->config->getDefaultPrompt($request, $store['store_id']);
                    if ($request === "cms") {
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
