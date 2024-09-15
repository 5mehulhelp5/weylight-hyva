<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\MagezonPageBuilder\Block\Element;

class ContactForm extends \Magezon\Builder\Block\Element
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getContactFormHtml()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $viewModel = $objectManager->get(\Magento\Contact\ViewModel\UserDataProvider::class);
        $contactForm = $this->getLayout()->createBlock(
            \Magento\Contact\Block\ContactForm::class,
            'contactForm' . rand(1, 100),
            [
                'data' => [
                    'view_model' => $viewModel
                ]
            ]
        )->setTemplate('Magento_Contact::form.phtml');

        return $contactForm->toHtml();
    }
}
