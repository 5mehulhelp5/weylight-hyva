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
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * GetConfigAdvancedHidePrice
 */
class GetConfigAdvancedHidePrice implements ResolverInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * Construct
     *
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * Response result
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        return [
            "enable" => $this->data->isEnable(),
            "selector" => $this->data->getSelector(),
            "email_sender" => $this->data->getEmailSender(),
            "admin_response_email_template" => $this->data->getAdminResponseEmailTemplate(),
            "admin_notify_email" => $this->data->getAdminEmailNotify(),
            "admin_notify_email_template" => $this->data->getAdminNotifyEmailTemplate(),
            "priority" => $this->data->getPriority(),
            "form_fields" => $this->data->getExtraField(),
            "is_show_customer_fields" => $this->data->isShowCustomerNameInfo(),
            "recaptcha" => $this->data->enableAntispam(),
            "site_key" => $this->data->getSiteKey(),
            "secret_key" => $this->data->getSecretKey(),
            "call_for_price_text" => $this->data->callForPriceText(),
            "call_for_price_categories" => $this->data->getCallForPriceCategories(),
            "call_for_price_customers" => $this->data->getCallForPriceCustomers(),
            "call_for_price_not_apply" => $this->data->callForPriceNotApply(),
            "hide_price_categories" => $this->data->getHidePriceCategories(),
            "hide_price_text" => $this->data->callHidePriceText(),
            "hide_price_customers" => $this->data->getHidePriceCustomers(),
            "hide_price_not_apply" => $this->data->hidePriceNotApply()
        ];
    }
}
