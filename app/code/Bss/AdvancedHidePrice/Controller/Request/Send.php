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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdvancedHidePrice\Controller\Request;

use Bss\AdvancedHidePrice\Helper\Data;
use Bss\AdvancedHidePrice\Helper\GetType;
use Bss\AdvancedHidePrice\Model\Request;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\Layout;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Zend_Validate_Exception;

/**
 * Class Send
 *
 * @package Bss\AdvancedHidePrice\Controller\Request
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Send extends Action
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Request
     */
    protected $model;

    /**
     * @var GetType
     */
    protected $getType;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var
     */
    protected $timezone;

    /**
     * @var null
     */
    protected $customerSession = null;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UploaderFactory
     */
    protected $fileUploader;

    /**
     * Send constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param Escaper $escaper
     * @param Request $model
     * @param GetType $getType
     * @param Validator $formKeyValidator
     * @param TimezoneInterface $timezone
     * @param Json $json
     * @param SerializerInterface $serializer
     * @param ProductFactory $productFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploader
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $helper,
        Escaper $escaper,
        Request $model,
        GetType $getType,
        Validator $formKeyValidator,
        TimezoneInterface $timezone,
        Json $json,
        SerializerInterface $serializer,
        ProductFactory $productFactory,
        Filesystem $filesystem,
        UploaderFactory $fileUploader
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->escaper = $escaper;
        $this->model = $model;
        $this->getType = $getType;
        $this->formKeyValidator = $formKeyValidator;
        $this->timezone = $timezone;
        $this->json = $json;
        $this->serializer = $serializer;
        $this->productFactory = $productFactory;
        $this->filesystem           = $filesystem;
        $this->fileUploader         = $fileUploader;
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface|Layout
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $data = $this->getRequest()->getPostValue();

        $this->getType->getInlineTranslation()->suspend();
        try {
            $this->checkData($data);
            if ($this->helper->enableAntispam()) {
                $recaptcha_response = $data["g-recaptcha-response"];
                $captchaError = true;
                if ($recaptcha_response) {
                    $captchaError = $this->getType->getRecaptcha()->verify($recaptcha_response);
                }
                $this->checkEmptyCaptchaError($captchaError);
            }

            $config = $this->helper->isShowCustomerNameInfo();
            //additional information
            $cloneData = array_merge([], $data);
            $fields = $this->helper->getExtraField();
            $customOptionData = $this->checkCustomOptionData(
                $cloneData,
                $this->checkProductId($data)
            );
            foreach ($cloneData as $key => $value) {
                if ($this->checkUnset($key)) {
                    unset($cloneData[$key]);
                } else {
                    if (isset($fields[$key])) {
                        $label = $fields[$key]['field_label'] . '__BssAdvancedHidePrice__'
                            . $key . '__BssAdvancedHidePrice__' . $fields[$key]['field_type'];
                        $cloneData[$key] = trim($this->escaper->escapeHtml($value));
                        $cloneData[$label] = $cloneData[$key];
                    }
                    unset($cloneData[$key]);
                }
            }

            /** @var Request $model */
            $model = $this->model;
            //general information
            $this->setCustomerNameandEmail($config, $model, $data);

            //check Throw exception
            $this->resultError($data, $config);

            $model->setProductIds(trim($this->escaper->escapeHtml($data['product_ids'])));
            $model->setProductName(trim($this->escaper->escapeHtml($data['product_name'])));
            if (!empty($customOptionData)) {
                $model->setRequiredOptions($this->serializer->serialize($customOptionData));
            }
            $model->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
            $model->setStoreId($this->helper->getStoreId());

            $cloneData = $this->helper->returnSerializer()->serialize($cloneData);
            $model->setContent($cloneData);
            $this->_eventManager->dispatch(
                'callforprice_before_save_form',
                ['formdata' => $model, 'request' => $this->getRequest()]
            );
            try {
                $model->save();
                $this->_eventManager->dispatch(
                    'callforprice_after_save_form',
                    ['formdata' => $model, 'request' => $this->getRequest()]
                );
                $this->messageManager->addSuccessMessage(
                    __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
                );
                $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
            $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        } catch (Exception $e) {
            $this->getType->getInlineTranslation()->resume();
            $this->messageManager->addErrorMessage(
                __($e->getMessage())
            );
            $resultRedirect = $this->resultFactory->create($this->getType->getTypeRedirect());
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }

    /**
     * Check Product Id
     *
     * @param array $data
     * @return string
     */
    private function checkProductId($data)
    {
        if (isset($data['parent_id']) && $data['parent_id']) {
            return trim($this->escaper->escapeHtml($data['parent_id']));
        }
        return trim($this->escaper->escapeHtml($data['product_ids']));
    }

    /**
     * Check Custom Option Data
     *
     * @param array $cloneData
     * @param int $productId
     * @return array
     */
    protected function checkCustomOptionData($cloneData, $productId)
    {
        $customOptionData = [];
        if (isset($cloneData['options'])) {
            $product = $this->productFactory->create()->load($productId);
            foreach ($product->getOptions() as $option) {
                $this->setCustomOptionData($option, $product, $cloneData, $customOptionData);
                try {
                    $file = $this->getRequest()->getFiles('options_' . $option->getOptionId() . '_file');
                    $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;
                    if ($file && $fileName) {
                        $target = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                        $customerEmail = isset($cloneData['customer_email']) ? $cloneData['customer_email'] : $this->returnCustomerSession()->getCustomer()->getEmail();
                        $targetPath = $target->getAbsolutePath(
                            'callprice/' . $productId . '/' . $customerEmail
                        );
                        /** @param $uploader \Magento\MediaStorage\Model\File\Uploader */
                        $uploader = $this->fileUploader->create(
                            [
                                'fileId' => 'options_' . $option->getOptionId() . '_file'
                            ]
                        );
                        // set allowed file extensions
                        $uploader->setAllowedExtensions(['jpeg','jpg', 'pdf', 'doc', 'png', 'zip']);
                        // allow folder creation
                        $uploader->setAllowCreateFolders(true);
                        // rename file name if already exists
                        $uploader->setAllowRenameFiles(true);
                        // upload file in the specified folder
                        $uploader->save($targetPath);
                        $customOptionData[] = [
                            'name' => $option->getTitle(),
                            'value' => $fileName,
                            'option_type' => 'file',
                            'price' => $option->getPrice()
                        ];
                    }
                } catch (Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
        return $customOptionData;
    }

    /**
     * Set Custom Option Data
     *
     * @param array $option
     * @param object $product
     * @param array $cloneData
     * @param array $customOptionData
     */
    protected function setCustomOptionData($option, $product, $cloneData, &$customOptionData)
    {
        $optionId = $option->getOptionId();

        if (isset($cloneData['options'][$optionId]) && !empty($cloneData['options'][$optionId])) {
            $typeSelectDrop = ['radio', 'dropdown'];
            $this->setTypeSelect($option, $product, $cloneData, $customOptionData);
            $this->setTypeDateTime($option, $product, $cloneData, $customOptionData);
            if (in_array($option->getType(), $typeSelectDrop)) {
                $cloneData['options'][$optionId] =[$cloneData['options'][$optionId]];
            }
            if ($option->getType() == 'date' && $cloneData['options'][$optionId]['month']) {
                $dateValue = implode('-', $cloneData['options'][$optionId]);
                $customOptionData[] = [
                    'name' => $option->getTitle(),
                    'value' => $dateValue,
                    'option_type' => 'date',
                    'price' => $this->addOptionPrice($option, $product)
                ];
            }
            if ($option->getType() == 'time' && $cloneData['options'][$optionId]['hour']) {
                $dateValue = implode(':', $cloneData['options'][$optionId]);
                $customOptionData[] = [
                    'name' => $option->getTitle(),
                    'value' => $dateValue,
                    'option_type' => 'date',
                    'price' => $this->addOptionPrice($option, $product)
                ];
            }
            if (is_string($cloneData['options'][$optionId])) {
                $customOptionData[] = [
                    'name' => $option->getTitle(),
                    'value' => $cloneData['options'][$optionId],
                    'option_type' => 'string',
                    'price' => $this->addOptionPrice($option, $product)
                ];
            }
        }
    }

    /**
     * Set Type Datetime
     *
     * @param array $option
     * @param object $product
     * @param array $cloneData
     * @param array $customOptionData
     */
    public function setTypeDateTime($option, $product, $cloneData, &$customOptionData)
    {
        if ($option->getType() == 'date_time') {
            $dateValue = $cloneData['options'][$option->getOptionId()];
            $dateValue['hour'] = 'space' . $dateValue['hour'];
            $dateValue['minute'] = ':' . $dateValue['minute'];
            $dateValue['day_part'] = 'space' . $dateValue['day_part'];
            $dateValue = implode("-", $dateValue);
            $dateValue = str_replace("-space", " ", $dateValue);
            $dateValue = str_replace("-:", ":", $dateValue);
            $customOptionData[] = [
                'name' => $option->getTitle(),
                'value' => $dateValue,
                'option_type' => 'date',
                'price' => $this->addOptionPrice($option, $product)
            ];
        }
    }

    /**
     * Set Type Select
     *
     * @param array $option
     * @param object $product
     * @param array $cloneData
     * @param array $customOptionData
     */
    public function setTypeSelect($option, $product, $cloneData, &$customOptionData)
    {
        $typeSelect = ['checkbox','multi','select','radio','drop_down'];

        if (in_array($option->getType(), $typeSelect)) {
//            $flipOption = array_flip($cloneData['options'][$option->getOptionId()]);
            $valueOption = [];
            $price = 0;
            foreach ($option->getValues() as $optionValues) {
                if (in_array($optionValues->getId(), $cloneData['options'])) {
                    $valueOption[]= $optionValues->getTitle();
                    $price += $this->addOptionPrice($optionValues, $product);
                }
            }
            $customOptionData[] = [
                'name' => $option->getTitle(),
                'value' => implode(",", $valueOption),
                'option_type' => 'select',
                'price' => $price
            ];
        }
    }

    /**
     * Add Option Price
     *
     * @param array $option
     * @param object $product
     * @return float
     */
    private function addOptionPrice($option, $product)
    {
        if ($option->getPriceType() == "percent") {
            return round((float)$product->getFinalPrice()/100 * (float)$option->getDefaultPrice(), 2);
        }
        return round((float)$option->getDefaultPrice(), 2);
    }

    /**
     * Return Customer Session
     *
     * @return Session|null
     */
    protected function returnCustomerSession()
    {
        if (!$this->customerSession) {
            $this->customerSession = $this->helper->getCustomerSession();
        }
        return $this->customerSession;
    }

    /**
     * Set Customer Name And Email
     *
     * @param string $config
     * @param Request $model
     * @param array $data
     */
    private function setCustomerNameandEmail($config, $model, $data)
    {
        if ($this->checkIsLoggin($config)) {
            $customerName = $this->returnCustomerSession()->getCustomer()->getName();
            $customerEmail = $this->returnCustomerSession()->getCustomer()->getEmail();
            $model->setCustomerName($customerName);
            $model->setCustomerEmail($customerEmail);
        } else {
            $model->setCustomerName(trim($this->escaper->escapeHtml($data['customer_name'])));
            $model->setCustomerEmail(trim($this->escaper->escapeHtml($data['customer_email'])));
        }
    }

    /**
     * Check Unset
     *
     * @param int $key
     * @return bool
     */
    private function checkUnset($key)
    {
        if ($key == 'product_name' || $key == 'product_ids' || $key == 'customer_name' || $key == 'customer_email' ||
            $key == 'g-recaptcha-response' || $key == 'antispam-recaptcha' || $key == 'form_key') {
            return true;
        }
        return false;
    }

    /**
     * Check Is Logged In
     *
     * @param string $config
     * @return bool
     */
    private function checkIsLoggin($config)
    {
        if ($this->returnCustomerSession()->isLoggedIn() && !$config) {
            return true;
        }
        return false;
    }

    /**
     * Is Allowed
     *
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdvancedHidePrice::request_access');
    }

    /**
     * Check Data
     *
     * @param array $data
     * @throws NoSuchEntityException
     * @throws Exception
     */
    private function checkData($data)
    {
        if (!$data) {
            throw new LocalizedException(
                __('Please enter data to the field.')
            );
        }

        $this->validateFormKey();
        $product = $this->_initProduct();
        $this->validateProducApply($product);
    }

    /**
     * Check Empty Captcha Error
     *
     * @param string $captchaError
     *
     * @throws LocalizedException
     */
    private function checkEmptyCaptchaError($captchaError)
    {
        if (!empty($captchaError)) {
            throw new LocalizedException(
                __('reCaptcha a required field.')
            );
        }
    }

    /**
     * Result Error
     *
     * @param array $data
     * @param string $config
     *
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    private function resultError($data, $config)
    {
        $error = false;
        if (!($this->returnCustomerSession()->isLoggedIn() && !$config)) {
            if (!\Zend_Validate::is(trim($this->escaper->escapeHtml($data['customer_email'])), 'EmailAddress')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($this->escaper->escapeHtml($data['customer_name'])), 'NotEmpty')) {
                $error = true;
            }
        }
        if (!\Zend_Validate::is(trim($this->escaper->escapeHtml($data['product_ids'])), 'NotEmpty')) {
            $error = true;
        }
        if (!\Zend_Validate::is(trim($this->escaper->escapeHtml($data['product_name'])), 'NotEmpty')) {
            $error = true;
        }
        if ($error) {
            throw new LocalizedException(__('has errors.'));
        }
    }

    /**
     * Validate Form Key
     *
     * @throws Exception
     */
    protected function validateFormKey()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(
                __('Invalid Form Key. Please refresh the page.')
            );
        }
    }

    /**
     * Init Product
     *
     * @return bool|ProductInterface
     * @throws NoSuchEntityException
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product_ids');
        if ($productId) {
            $storeId = $this->getType->getStoreManager()->getStore()->getId();
            try {
                return $this->helper->getProductById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(
                    __('Product is not exits')
                );
                $this->_redirect('*/*/');
                return false;
            }
        }
        return false;
    }

    /**
     * Configurable Product
     *
     * @return bool|ProductInterface
     * @throws NoSuchEntityException
     */
    protected function configurableProduct()
    {
        $parentId = $this->getRequest()->getParam('parent_id');
        if ($parentId) {
            $storeId = $this->getType->getStoreManager()->getStore()->getId();
            try {
                return $this->helper->getProductById($parentId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(
                    __('Product is not exits')
                );
                $this->_redirect('*/*/');
                return false;
            }
        }
        return false;
    }

    /**
     * Validate Product Apply
     *
     * @param  object $product
     * @throws Exception
     */
    protected function validateProducApply($product)
    {
        $storeId = $this->getType->getStoreManager()->getStore()->getId();
        if ($this->configurableProduct()) {
            $check = $this->helper->activeCallHidePriceConfigurable($this->configurableProduct(), $storeId);
            if ($check) {
                if (!$this->helper->activeCallHidePrice($this->configurableProduct())) {
                    throw new LocalizedException(
                        __('Sorry, Product not aplly call for price')
                    );
                }
            } else {
                if (!$this->helper->activeCallHidePrice($product)) {
                    throw new LocalizedException(
                        __('Sorry, Product not aplly call for price')
                    );
                }
            }
        } else {
            if (!$this->helper->activeCallHidePrice($product)) {
                throw new LocalizedException(
                    __('Sorry, Product not aplly call for price')
                );
            }
        }
    }
}
