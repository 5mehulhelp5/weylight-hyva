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
namespace Bss\ChatGPT\Model;

use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /* Config ChatGPT */
    public const CHAT_GPT_CONFIG_CHATGPT_ENABLE = 'chat_gpt/config_chatgpt/enable';
    //public const CHAT_GPT_CONFIG_CHATGPT_API_URL = 'chat_gpt/config_chatgpt/api_url';
    public const CHAT_GPT_CONFIG_CHATGPT_API_KEY = 'chat_gpt/config_chatgpt/api_key';
    public const CHAT_GPT_CONFIG_CHATGPT_MODEL = 'chat_gpt/config_chatgpt/model';
    public const CHAT_GPT_CONFIG_CHATGPT_TEMPERATURE = 'chat_gpt/config_chatgpt/temperature';
    public const CHAT_GPT_CONFIG_CHATGPT_MAX_TOKENS = 'chat_gpt/config_chatgpt/max_tokens';
    public const CHAT_GPT_CONFIG_CHATGPT_IS_ACTIVE = 'chat_gpt/config_chatgpt/is_active';

    /* Default message */
    public const CHAT_GPT_PRODUCT_SYSTEM_ROLE = 'chat_gpt/default_message/group_product/system_role';
    public const CHAT_GPT_MESSAGE_PRODUCT = 'chat_gpt/default_message/group_product/product';

    public const CHAT_GPT_CATEGORY_SYSTEM_ROLE = 'chat_gpt/default_message/group_category/system_role';
    public const CHAT_GPT_MESSAGE_CATEGORY = 'chat_gpt/default_message/group_category/category';

    public const CHAT_GPT_CMS_SYSTEM_ROLE = 'chat_gpt/default_message/group_cms/system_role';
    public const CHAT_GPT_MESSAGE_CMS = 'chat_gpt/default_message/group_cms/cms';

    public const CHAT_GPT_SEO_SYSTEM_ROLE = 'chat_gpt/default_message/group_seo/system_role';
    public const CHAT_GPT_MESSAGE_SEO_TITLE = 'chat_gpt/default_message/group_seo/seo_title';
    public const CHAT_GPT_MESSAGE_SEO_KEYWORD = 'chat_gpt/default_message/group_seo/seo_keyword';
    public const CHAT_GPT_MESSAGE_SEO_DESCRIPTION = 'chat_gpt/default_message/group_seo/seo_description';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Construct.
     *
     * @param Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Module is enable.
     *
     * @return int
     */
    public function isEnable()
    {
        return (int)$this->scopeConfig->getValue(self::CHAT_GPT_CONFIG_CHATGPT_ENABLE);
    }

    /**
     * API url ChatGPT
     *
     * @return string
     */
    public function getApiUrl()
    {
        //return (string)$this->scopeConfig->getValue(self::CHAT_GPT_CONFIG_CHATGPT_API_URL);
        return 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * API key ChatGPT
     *
     * @return string
     */
    public function getApiKey()
    {
        return (string)$this->scopeConfig->getValue(self::CHAT_GPT_CONFIG_CHATGPT_API_KEY);
    }

    /**
     * API model ChatGPT
     *
     * @return string
     */
    public function getModelType()
    {
        return (string)$this->scopeConfig->getValue(self::CHAT_GPT_CONFIG_CHATGPT_MODEL);
    }

    /**
     * API temperature ChatGPT. (0 < temperature <= 1)
     *
     * @return float
     */
    public function getTemperature()
    {
        return (float)$this->scopeConfig->getValue(self::CHAT_GPT_CONFIG_CHATGPT_TEMPERATURE);
    }

    /**
     * API max tokens ChatGPT
     *
     * @param int $storeId
     * @return int
     */
    public function getMaxTokens($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::CHAT_GPT_CONFIG_CHATGPT_MAX_TOKENS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Config ChatGPT is ready
     *
     * @param int $storeId
     * @return int
     */
    public function isActiveApiKey($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::CHAT_GPT_CONFIG_CHATGPT_IS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default system role
     *
     * @param string $request
     * @param int $storeId
     * @return string
     */
    public function getDefaultSystemRole($request, $storeId = null)
    {
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        switch ($request) {
            case "category":
                $path = self::CHAT_GPT_CATEGORY_SYSTEM_ROLE;
                break;
            case "cms":
                $path = self::CHAT_GPT_CMS_SYSTEM_ROLE;
                $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                break;
            case "seo_keyword":
            case "seo_description":
            case "seo_title":
                $path = self::CHAT_GPT_SEO_SYSTEM_ROLE;
                break;
            default:
                $path = self::CHAT_GPT_PRODUCT_SYSTEM_ROLE;
                break;
        }

        return (string)$this->scopeConfig->getValue(
            $path,
            $scopeType,
            $storeId
        );
    }

    /**
     * Get default prompt
     *
     * @param string $request
     * @param int $storeId
     * @return string
     */
    public function getDefaultPrompt($request, $storeId = null)
    {
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        switch ($request) {
            case "category":
                $path = self::CHAT_GPT_MESSAGE_CATEGORY;
                break;
            case "cms":
                $path = self::CHAT_GPT_MESSAGE_CMS;
                $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                break;
            case "seo_title":
                $path = self::CHAT_GPT_MESSAGE_SEO_TITLE;
                break;
            case "seo_keyword":
                $path = self::CHAT_GPT_MESSAGE_SEO_KEYWORD;
                break;
            case "seo_description":
                $path = self::CHAT_GPT_MESSAGE_SEO_DESCRIPTION;
                break;
            default:
                $path = self::CHAT_GPT_MESSAGE_PRODUCT;
                break;
        }

        $message = (string)$this->scopeConfig->getValue(
            $path,
            $scopeType,
            $storeId
        );
        return strip_tags($message);
    }
}
