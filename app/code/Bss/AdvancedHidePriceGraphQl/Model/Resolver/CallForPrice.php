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
 * @package    Bss_AdvancedHidePriceGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdvancedHidePriceGraphQl\Model\Resolver;

use Bss\AdvancedHidePrice\Helper\Data;
use Bss\AdvancedHidePrice\Model\Request;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Setup\Exception;
use Safe\Exceptions\JsonException;

/**
 * Call For Price
 */
class CallForPrice implements ResolverInterface
{
    /**
     * Const
     */
    public const BSS_ADVANCED_HIDE_PRICE = '__BssAdvancedHidePrice__';
    public const MESSAGE_CHECK_INVALID = "Invalid parameter list. Please enter filed and send again";
    public const MESSAGE_CALL_PRICE_ERROR = "Error for call hide price";

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Request
     */
    protected $model;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var CustomerRepository
     */
    protected $customer;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Construct
     *
     * @param ProductRepository $productRepository
     * @param Request $model
     * @param TimezoneInterface $timezone
     * @param Data $data
     * @param CustomerRepository $customer
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductRepository   $productRepository,
        Request             $model,
        TimezoneInterface   $timezone,
        Data                $data,
        CustomerRepository  $customer,
        SerializerInterface $serializer
    ) {
        $this->productRepository = $productRepository;
        $this->model = $model;
        $this->timezone = $timezone;
        $this->data = $data;
        $this->customer = $customer;
        $this->serializer = $serializer;
    }

    /**
     * Graphql response
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws NoSuchEntityException|GraphQlInputException
     * @throws \Exception
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ): array {
        $this->validateInput($args);
        $product = $this->getProductBySku($args['sku_product']);
        $model = $this->model;
        $model = $this->setDataToModel($product, $model, $context, $args);
        try {
            $model->save();
            return [
                "status" => "success",
                "store_id" => $model->getStoreId(),
                "customer_name" => $model->getCustomerName(),
                "customer_email" => $model->getCustomerEmail(),
                "created_at" => $model->getCreatedAt(),
                "product_id" => $model->getProductIds(),
                "product_name" => $model->getProductName(),
                "extra_field" => $model->getContent()
            ];
        } catch (Exception $e) {
            throw new GraphQlInputException(__(self::MESSAGE_CALL_PRICE_ERROR));
        }
    }

    /**
     * Validate input
     *
     * @param array|null $args
     * @return void
     * @throws GraphQlInputException
     */
    public function validateInput(array $args = null)
    {
        if (empty($args['sku_product']) || empty($args['name']) || empty($args['mail'])
        ) {
            throw new GraphQlInputException(__(self::MESSAGE_CHECK_INVALID));
        }
    }

    /**
     * Get product by sku input
     *
     * @param String $sku
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    public function getProductBySku(string $sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * Prepare set data to model before save to db
     *
     * @param Product $product
     * @param Request $model
     * @param $context
     * @param array|null $args
     * @return Request
     * @throws JsonException
     * @throws NoSuchEntityException
     */
    public function setDataToModel(
        Product $product,
        Request $model,
        $context,
        array   $args = null
    ): Request {
        if (sizeof($this->data->getExtraField()) == 0 || !array_key_exists('extra_field', $args)) {
            $cloneData = "[]";
        } else {
            $cloneData = $this->data->returnSerializer()->serialize($this->getAdditionalInformation($args['extra_field']));
        }
        $model->setCustomerName($args['name']);
        $model->setCustomerEmail($args['mail']);
        $model->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
        $model->setStoreId($this->data->getStoreId());
        $model->setProductIds(trim($product->getId()));
        $model->setProductName(trim($product->getName()));
        $model->setContent($cloneData);
        return $model;
    }

    /**
     * Get data in Call For Price Form but using input graphql
     *
     * @param string $jsonString
     * @return array
     */
    public function getAdditionalInformation($jsonString)
    {
        $extraFieldInput = $this->serializer->unserialize($jsonString);
        $additionalInformation = $this->data->getExtraField();
        if (sizeof($additionalInformation) != 0) {
            foreach ($extraFieldInput as $key => $value) {
                $extraField = $this->checkExtraFieldInput($key, $additionalInformation);
                if (!empty($extraField)) {
                    $label = $extraField['field_label'] . self::BSS_ADVANCED_HIDE_PRICE
                        . $extraField['key'] . self::BSS_ADVANCED_HIDE_PRICE . $extraField['field_type'];
                    $extraFieldInput[$key] = $value;
                    $extraFieldInput[$label] = $extraFieldInput[$key];
                }
                unset($extraFieldInput[$key]);
            }
            return $extraFieldInput;
        }
        return [];
    }

    /**
     * Check key of extra field is owner input value and get some information off this field
     *
     * @param string $extraFieldInput
     * @param array $additionalInformation
     * @return array
     */
    public function checkExtraFieldInput($extraFieldInput, $additionalInformation)
    {
        foreach ($additionalInformation as $key => $value) {
            if ($value['field_label'] == $extraFieldInput) {
                return [
                    "key" => $key,
                    "field_label" => $value['field_label'],
                    "field_type" => $value['field_type']
                ];
            }
        }
        return [];
    }
}
