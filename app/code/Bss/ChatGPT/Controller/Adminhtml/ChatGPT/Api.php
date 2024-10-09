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
namespace Bss\ChatGPT\Controller\Adminhtml\ChatGPT;

use Magento\Backend\App\Action\Context;

class Api extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * Number of "tokens" safe to not exceed ChatGPT.
     */
    public const CHATGPT_TOKENS_SAFE = 10;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Bss\ChatGPT\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Construct.
     *
     * @param Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\ChatGPT\Model\Config $config
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\ChatGPT\Model\Config $config,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->config = $config;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * API execute.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $request = $this->getRequest()->getParams() ?? [];
        $content = [
            "success" => "",
            "error" => ""
        ];

        if (!$this->config->isEnable()) {
            $content["error"] = __("Module ChatGPT is disabled!");
            return $this->contentToJson($content);
        }

        if (isset($request['search'])) {
            $search = (string)$request['search'];

            // Remove '{{', '}}' before call API.
            $search = str_replace("{{", " ", $search);
            $search = str_replace("}}", " ", $search);

            $dataApi['system_role'] = $request['system_role'] ?? "";
            $dataApi['prompt'] = $search;
        } else {
            $content["error"] = __("Request prompt null!");
            return $this->contentToJson($content);
        }

        if (empty($request['test_key'])) {
            /* Config general */
            $this->getConfigApi($dataApi);
        } else {
            /* Call ChatGPT Test */
            $this->getConfigApiTest($dataApi, $request);
        }

        $this->callChatGPT($dataApi, $content);

        return $this->contentToJson($content);
    }

    /**
     * Create json factory.
     *
     * @param array $content
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function contentToJson($content)
    {
        $response = ['data' => $content];
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);

        return $resultJson;
    }

    /**
     * Call API
     *
     * @param array $request
     * @param array $content
     * @return array
     */
    public function callChatGPT($request, &$content)
    {
        try {
            $maxTokens = $this->calculateMaxTokens(
                (int)$request['max_tokens'],
                $request['system_role'] . $request['prompt'],
                $content
            );

            if (!$maxTokens) {
                return $content;
            }

            $payload = $this->json->serialize([
                "messages" => [
                    [
                        'role' => 'system',
                        'content' => $request['system_role']
                    ],
                    [
                        'role' => 'user',
                        'content' => $request['prompt']
                    ]
                ],
                "model" => $request['model'],
                "temperature" => $request['temperature'],
                "max_tokens" => $maxTokens
            ]);

            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer " . $request['api_key']);
            $this->curl->setOptions(
                [
                    CURLOPT_URL => $request['url'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload
                ]
            );
            $this->curl->post($request['url'], []);
            $response = $this->curl->getBody();

            $result = $this->json->unserialize($response);

            if (isset($result['error']['message'])) {
                $errMessChatGPT = $result['error']['message'] . " " . $result['error']['code'] ?? "";
                $this->logger->error($errMessChatGPT);
                $content["error"] = $errMessChatGPT;
            }

            $contentAI = "";
            if (isset($result['choices'][0]['message']['content'])) {
                /* Validate content AI */
                $contentAI = preg_replace(
                    '/^[\s\r\n]*(\'|"|\s)+|(\'|"|\s)+$/',
                    '',
                    $result['choices'][0]['message']['content']
                );
            }
            $content["success"] = $contentAI;

            return $content;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $content["error"] = $e->getMessage();
            return $content;
        }
    }

    /**
     * Get all config
     *
     * @param array $result
     * @return array
     */
    public function getConfigApi(&$result)
    {
        $result['url'] = $this->config->getApiUrl();
        $result['api_key'] = $this->config->getApiKey();
        $result['model'] = $this->config->getModelType();
        $result['temperature'] = $this->config->getTemperature();
        $result['max_tokens'] = $this->config->getMaxTokens();

        return $result;
    }

    /**
     * Get config test
     *
     * @param array $result
     * @param array $request
     * @return array
     */
    public function getConfigApiTest(&$result, $request)
    {
        $result['url'] = $this->config->getApiUrl();
        $result['api_key'] = (int)$request['changed_key']
            ? (string)$request['api_key']
            : $this->config->getApiKey();
        $result['model'] = (string)$request['model'];
        $result['temperature'] = (float)$request['temperature'];
        $result['max_tokens'] = (int)$request['max_tokens'];
        $result['system_role'] = (string)$request['system_role'];

        return $result;
    }

    /**
     * Calculate token before call API
     *
     * @param int $tokenConfig
     * @param string $request
     * @param array $content
     * @return int
     */
    public function calculateMaxTokens($tokenConfig, $request, &$content)
    {
        if (!$request) {
            return 0;
        }

        // 1 token generally corresponds to ~4 characters of text for common English text.
        $promptTokens = round(strlen($request) / 4);
        $tokens = $tokenConfig - $promptTokens - self::CHATGPT_TOKENS_SAFE;

        if ($tokens < self::CHATGPT_TOKENS_SAFE) {
            $this->logger->error(__("Config Max Tokens (API ChatGPT) is too low."));
            $content["error"] = __("Config Max Tokens (API ChatGPT) is too low.");
            return 0;
        }

        return $tokens;
    }
}
