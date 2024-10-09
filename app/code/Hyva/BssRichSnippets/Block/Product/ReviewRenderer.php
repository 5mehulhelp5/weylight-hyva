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
 * @package    Hyva_BssRichSnippets
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-present BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Hyva\BssRichSnippets\Block\Product;

use Bss\RichSnippets\Block\Product\ReviewRenderer as RichSnippetsReviewRenderer;

class ReviewRenderer extends RichSnippetsReviewRenderer
{
    /**
     * @var \Bss\RichSnippets\Helper\Data
     */
    protected $helper;

    /**
     * @var array
     */
    protected $_availableTemplates;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Bss\RichSnippets\Helper\Data $helper
     * @param array $availableTemplates
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Bss\RichSnippets\Helper\Data $helper,
        array $availableTemplates = [
            self::FULL_VIEW => 'Hyva_BssRichSnippets::helper/summary.phtml',
            self::SHORT_VIEW => 'Magento_Review::helper/summary_short.phtml'
        ],
        array $data = []
    ) {
        $this->_availableTemplates = $availableTemplates;
        $this->helper = $helper;
        $this->reviewFactory = $reviewFactory;
        
        parent::__construct(
            $context,
            $reviewFactory,
            $helper,
            $availableTemplates,
            $data
        );
    }
}
