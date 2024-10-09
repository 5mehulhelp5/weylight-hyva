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

class ChatGPT
{
    public const CHAT_GPT_LIST_MESSAGE = [
        'product',
        'category',
        'cms',
        'seo_title',
        'seo_keyword',
        'seo_description'
    ];

    /**
     * @var array
     */
    protected $skipAttribute = [
        'short_description',
        'description',
        'category_ids'
    ];

    /**
     * @var \Bss\ChatGPT\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Construct.
     *
     * @param \Bss\ChatGPT\Model\Config $config
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Bss\ChatGPT\Model\Config $config,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->config = $config;
        $this->url = $url;
        $this->json = $json;
    }

    /**
     * Get all mess default in general config
     *
     * @param int $storeId
     * @return bool|string
     */
    public function getAllMessDefault($storeId = null)
    {
        $result = [];

        foreach (self::CHAT_GPT_LIST_MESSAGE as $request) {
            $result[$request]['role'] = $this->config->getDefaultSystemRole($request, $storeId);
            $result[$request]['prompt'] = $this->config->getDefaultPrompt($request, $storeId);
        }

        return $this->json->serialize($result);
    }

    /**
     * Get url call API
     *
     * @param string $path
     * @return string
     */
    public function getUrlChatGPT($path = 'chatgpt/chatgpt/api')
    {
        return $this->url->getUrl($path);
    }

    /**
     * Get all attributes of product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAllAttributes($product)
    {
        $result = [];
        $allAttributes = $product->getAttributes();

        foreach ($allAttributes as $attribute) {
            if (in_array($attribute->getAttributeCode(), $this->skipAttribute)) {
                continue;
            }
            if ($label = $attribute->getStoreLabel()) {
                try {
                    if ($attribute->usesSource()) {
                        $value = $product->getAttributeText($attribute->getAttributeCode());
                    } else {
                        $value = $product->getData($attribute->getAttributeCode());
                    }

                    if ($value instanceof \Magento\Framework\Phrase) {
                        $value = $value->getText();
                    }
                    if (is_array($value)) {
                        $value = implode(",", $value);
                    }
                } catch (\Exception $e) {
                    continue;
                }

                if ($value) {
                    $result['product[' . $attribute->getAttributeCode() . ']']['label'] = strip_tags($label);
                    $result['product[' . $attribute->getAttributeCode() . ']']['value'] = strip_tags($value);
                }
            }
        }

        return $result;
    }
}
