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
use Bss\AdvancedHidePrice\Helper\GetType;
use Bss\AdvancedHidePrice\Model\Request;
use Exception;
use Magento\Framework\Escaper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * SendEmail
 */
class SendEmail implements ResolverInterface
{

    /**
     * Const
     */
    public const ADMIN_ACCOUNT_TYPE = 2;
    public const MESSAGE_AUTHORIZED = "You are not authorized to send email";
    public const MESSAGE_CHECK_INVALID = "Invalid parameter list. Please enter filed and send again";
    public const MESSAGE_SEND_ERROR = "Can't send email. Something went wrong, please try again !";
    public const MESSAGE_SEND_SUCCESS = "Success! A Email Was Sent To Customer";
    public const BSS_ADVANCED_HIDE_PRICE = '__BssAdvancedHidePrice__';
    public const MESSAGE_LOAD_BY_ID_ERROR = 'Id product inquiry not found';

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Request
     */
    protected $model;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var GetType
     */
    protected $getType;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * Construct
     *
     * @param Data $data
     * @param Request $model
     * @param TransportBuilder $transportBuilder
     * @param GetType $getType
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     */
    public function __construct(
        Data             $data,
        Request          $model,
        TransportBuilder $transportBuilder,
        GetType          $getType,
        StateInterface   $inlineTranslation,
        Escaper          $escaper
    ) {
        $this->data = $data;
        $this->model = $model;
        $this->transportBuilder = $transportBuilder;
        $this->getType = $getType;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
    }

    /**
     * Prepare and send email to user
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ): array {
        $this->validateInput($context, $args);
        $idCustomerInquiry = $args['id'];
        $priceProduct = $args['price'];
        $model = $this->model;
        $senderEmail = $this->data->getEmailSender();
        $senderName = $this->data->getEmailSenderName();
        $model->load($idCustomerInquiry);
        if (!$model->getId()) {
            throw new GraphQlInputException(__(self::MESSAGE_LOAD_BY_ID_ERROR));
        } else {
            $customerName = $model->getCustomerName();
            $customerEmail = $model->getCustomerEmail();
            $additional = $this->data->returnSerializer()->unserialize($model->getContent());
            $additionalHtml = "";
            foreach ($additional as $key => $value) {
                $label = explode(self::BSS_ADVANCED_HIDE_PRICE, $key)[0];
                $type = explode(self::BSS_ADVANCED_HIDE_PRICE, $key)[2];
                $additionalHtml .= $this->getType->fieldToHtmlEmail($label, $type, $value);
            }
            $date = $model->getCreatedAt();
            $this->inlineTranslation->suspend();
            try {
                $sender = [
                    'name' => $this->escaper->escapeHtml($senderName),
                    'email' => $this->escaper->escapeHtml($senderEmail)
                ];
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($this->data->getAdminResponseEmailTemplate())
                    ->setTemplateOptions(
                        [
                            'area' => $this->getType->getAreaFrontend(),
                            'store' => $this->getType->getStoreManager()->getStore()->getId()
                        ]
                    )
                    ->setTemplateVars([
                        'customer_name' => $customerName,
                        'product_price' => $priceProduct,
                        'product_name' => $model->getProductName(),
                        'date' => $date,
                        'additional_field' => $additionalHtml
                    ])->setFrom($sender)
                    ->addTo($customerEmail)
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $model->setEmailSent(1);
                $model->save();
                return [
                    "status" => self::MESSAGE_SEND_SUCCESS
                ];
            } catch (Exception $e) {
                throw new GraphQlInputException(__(self::MESSAGE_SEND_ERROR));
            }
        }
    }

    /**
     * Check input validate before send email to user
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @return void
     * @throws GraphQlInputException
     */
    public function validateInput($context, array $args = null)
    {
        if ($context->getUserType() != self::ADMIN_ACCOUNT_TYPE) {
            throw new GraphQlInputException(__(self::MESSAGE_AUTHORIZED));
        }
        if (empty($args['id']) || empty($args['price'])) {
            throw new GraphQlInputException(__(self::MESSAGE_CHECK_INVALID));
        }
    }
}
