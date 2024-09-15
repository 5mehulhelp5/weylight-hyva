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
namespace Bss\ChatGPT\Plugin\Data\Form\Element;

class Editor
{
    /**
     * @var \Bss\ChatGPT\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\View\Helper\SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * Construct.
     *
     * @param \Bss\ChatGPT\Model\Config $config
     * @param \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer
     */
    public function __construct(
        \Bss\ChatGPT\Model\Config $config,
        \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer
    ) {
        $this->config = $config;
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Add button ChatGPT in page builder
     *
     * @param \Magento\Framework\Data\Form\Element\Editor $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetElementHtml(\Magento\Framework\Data\Form\Element\Editor $subject, $result)
    {
        if (!$this->config->isEnable() || !$result) {
            return $result;
        }

        $hasJs = false;
        switch ($result) {
            case strpos($result, 'id="product_form_short_description"') !== false:
                $btnId = "chatgpt-product_short_description";
                break;
            case strpos($result, 'id="buttonsproduct_form_description"') !== false:
                $btnId = "chatgpt-product_description";
                break;
            case strpos($result, 'id="buttonscategory_form_description"') !== false:
                $btnId = "chatgpt-category_description";
                break;
            case strpos($result, 'id="buttonscms_page_form_content"') !== false:
                $btnId = "chatgpt-cms_page_description";
                break;
            case strpos($result, 'id="buttonspagebuilder_text_form_content"') !== false:
                $btnId = "chatgpt-pagebuilder_text";
                $hasJs = true;
                break;
            default:
                return $result;
        }

        $js = $hasJs ? $this->secureRenderer->renderTag(
                'script',
                ['type' => 'text/javascript'],
                /** @lang text */ <<<script
                require([
                    'jquery',
                    'chat_gpt-modal'
                ], function($, modalChatGPT) {
                    $('#chatgpt-pagebuilder_text').click(function() {
                        var element = $('#pagebuilder_text_form_content');
                        var data = [];
                        data['id'] = 'page_builder-text';
                        data['url'] = window.urlApi;
                        data['form_key'] = window.formKey;
                        data['role_default'] = window.roleDefaultPageBuilderText;
                        data['mess_default'] = window.messDefaultPageBuilderText;

                        modalChatGPT(element, data);
                    });
                });
                script,
                false
            ) : '';

        $chatGPT =
            '<button type="button" class="action-primary chatgpt" id="' . $btnId .'">
                <span>' . __('ChatGPT Content') . '</span>
            </button>' . $js;

        return preg_replace(
            '/(<button[^>]*>)(.*?)(<\/button>)(.*?)(<script[^>]*>)/s',
            '$1$2</button>' . $chatGPT . '$4$5',
            $result,
            1
        );
    }
}
