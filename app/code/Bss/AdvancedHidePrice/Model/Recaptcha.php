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
namespace Bss\AdvancedHidePrice\Model;

class Recaptcha extends \Magento\Framework\Model\AbstractModel
{
    const REQUEST_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var \Zend_Http_Client
     */
    protected $client;
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteip;

    /**
     * Recaptcha constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Zend_Http_Client $client
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteip
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Zend_Http_Client $client,
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteip,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->client = $client;
        $this->helper = $helper;
        $this->remoteip = $remoteip;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $recaptcha_response
     * @return string
     * @throws \Zend_Http_Client_Exception
     */
    public function verify($recaptcha_response)
    {
        $params = [
            'secret'   => $this->helper->getSecretKey(),
            'response' => $recaptcha_response,
            'remoteip' => $this->remoteip->getRemoteAddress(),
        ];
        
        $client = $this->getHttpClient();
        $client->setParameterPost($params);
        $errors = '';

        try {
            $response = $client->request('POST');
            $data = $this->helper->returnJson()->unserialize($response->getBody());
            if (array_key_exists('error-codes', $data)) {
                $errors = $data['error-codes'];
            }
        } catch (\Exception $e) {
            $data = ['success' => false];
        }

        return $errors;
    }

    /**
     * @param Varien_Http_Client $client
     * @return $this
     */
    public function setHttpClient(Varien_Http_Client $client)
    {
        $this->client = $client;
        
        return $this;
    }

    /**
     * @return \Zend_Http_Client
     * @throws \Zend_Http_Client_Exception
     */
    public function getHttpClient()
    {
                
        $this->client->setUri(self::REQUEST_URL);

        return $this->client;
    }
}
