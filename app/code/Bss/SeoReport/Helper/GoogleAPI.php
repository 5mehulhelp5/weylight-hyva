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
 * @package    Bss_SeoReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SeoReport\Helper;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class GoogleAPI
{
    const URL_OAUTH_2 = 'https://oauth2.googleapis.com/token';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var Json
     */
    private $jsonHelper;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @param Json $jsonHelper
     * @param LoggerInterface $logger
     * @param Data $dataHelper
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Json $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Bss\SeoReport\Helper\Data $dataHelper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Get token user
     *
     * @param string $code
     * @return bool|mixed|string
     */
    public function getTokenUser($code)
    {
        $redirectUri = $this->dataHelper->getGoogleApiUris();
        $clientId = $this->dataHelper->getClientId();
        $clientSecret = $this->dataHelper->getClientSecret();
        $userField = 'code=' . $code . '&client_id=' . $clientId . '&client_secret=' . $clientSecret .
            '&redirect_uri=' . $redirectUri . '&grant_type=authorization_code';
        $result = $this->handleTokenUser($userField);
        return $result;
    }

    /**
     * Refresh token user
     *
     * @param string $refreshCode
     * @return bool|mixed|string
     */
    public function refreshTokenUser($refreshCode)
    {
        $clientId = $this->dataHelper->getClientId();
        $clientSecret = $this->dataHelper->getClientSecret();
        $userField = 'refresh_token=' . $refreshCode . '&client_id=' . $clientId . '&client_secret=' . $clientSecret .
            '&grant_type=refresh_token';
        $result = $this->handleTokenUser($userField);
        return $result;
    }
    /**
     * Handle token user
     *
     * @param string $postField
     * @return bool|mixed|string
     */
    public function handleTokenUser($postField)
    {
        $response = '';
        try {
            $url = self::URL_OAUTH_2;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postField);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $response = $this->jsonHelper->unserialize($response, true);
            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $response;
    }

    /**
     * Get site info
     *
     * @param string $siteUrl
     * @param string $accessToken
     * @param string $tokenType
     * @param $dataJson
     * @return bool|mixed|string
     */
    public function getSiteInfo($siteUrl, $accessToken, $tokenType, $dataJson)
    {
        $siteUrl = $siteUrl !== null ? urlencode($siteUrl) : "";
        $url = 'https://www.googleapis.com/webmasters/v3/sites/' . $siteUrl . '/searchAnalytics/query';

        $response = '';
        try {
            $timeout = 20;
            $ch = curl_init();
            $authorization = 'Authorization: ' . $tokenType . ' ' . $accessToken;
            $postHeader = [
                'Content-Type: application/json',
                'Accept: application/json',
                $authorization
            ];

            curl_setopt(
                $ch,
                CURLOPT_USERAGENT,
                "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1"
            );
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $postHeader);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, "");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $response = curl_exec($ch);
            curl_close($ch);
            $response = $this->jsonHelper->unserialize($response, true);
            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return $response;
    }

    /**
     * Get google console by keyword
     *
     * @param string $startDate
     * @param string $endDate
     * @param $dimensionsObject
     * @param array $filterObject
     * @param int $limit
     * @param int $offset
     * @return false|string
     */
    public function getGoogleConsoleByKeyword(
        $startDate,
        $endDate,
        $dimensionsObject,
        $filterObject = [],
        $limit = 10,
        $offset = 0
    ) {
        $objectReturn = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        $objectReturn['dimensions'] = [];
        $objectReturn['dimensions']  = array_merge($objectReturn['dimensions'], $dimensionsObject);
        if (!empty($filterObject)) {
            $objectReturn['dimensionFilterGroups'][] = [
                "groupType" => "and",
                "filters" => $filterObject
            ];
        }
        $objectReturn['rowLimit'] = $limit;
        $objectReturn['startRow'] = $offset;

        $dataEncode = $this->jsonHelper->serialize($objectReturn);
        return $dataEncode;
    }
}
