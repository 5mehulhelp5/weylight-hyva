<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabelGraphQl
 * @author    Extension Team
 * @copyright Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductLabelGraphQl\Model\Resolver;

use Bss\ProductLabel\Helper\Data as HelperData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

/**
 * Class LabelId get product label by label id
 */
class LabelId implements ResolverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var ValueFactory
     */

    private $valueFactory;

    /**
     * LabelId constructor.
     *
     * @param HelperData $helperData
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        HelperData $helperData,
        ValueFactory $valueFactory
    ) {
        $this->helperData = $helperData;
        $this->valueFactory = $valueFactory;
    }

    /**
     *  Get information product label by id
     *
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return \Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['product_label_id'])) {
            throw new GraphQlAuthorizationException(
                __(
                    'product_label_id should be specified'
                )
            );
        }

        try {
            $data = $this->helperData->getProductLabelById($args['product_label_id']);

            $result = function () use ($data) {
                return !empty($data) ? $data : [];
            };

            return $this->valueFactory->create($result);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }
}
