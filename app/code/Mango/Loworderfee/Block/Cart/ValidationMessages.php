<?php

namespace Mango\Loworderfee\Block\Cart;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

class ValidationMessages extends \Magento\Checkout\Block\Cart\ValidationMessages
{

    /**
     * @var \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     */
    private $minimumAmountErrorMessage;
    
    protected $helper;
    protected $validationMessage;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Mango\Loworderfee\Helper\Data $helper,
        \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage $validationMessage,
        $data = []
    ) {
    
        $this->helper = $helper;
        $this->minimumAmountErrorMessage = $validationMessage;
        
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $cartHelper,
            $currency,
            $data
        );
    }

    /**
     * Validate minimum amount and display notice in error
     *
     * @return void
     */
    protected function validateMinimumAmount()
    {
        $websiteId = $this->cartHelper->getQuote()->getStore()->getWebsiteId();
        $minOrderActive = $this->helper->getWebsiteConfigValue(
            'active',
            $websiteId
        );
        $lowOrderFeeActive = $this->helper->getWebsiteConfigValue(
            'low_order_fee_active',
            $websiteId
        );
        /* added first order validation */
        if ($minOrderActive) {
            $_min_order_validation = true;
            if ($lowOrderFeeActive) {
                /* do not check address, do not check add notice or anything... */
                $_min_order_validation = $this->cartHelper->getQuote()->validateMinimumAmount(false, true);
            } else {
                $_min_order_validation = $this->cartHelper->getQuote()->validateMinimumAmount();
            }
            if (!$_min_order_validation) {
                $this->messageManager->addNotice($this->getMinimumAmountErrorMessage()->getMessage());
            }
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     * @deprecated
     */
    private function getMinimumAmountErrorMessage()
    {
        return $this->minimumAmountErrorMessage;
    }
}
