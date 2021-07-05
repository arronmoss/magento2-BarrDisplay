<?php

/**
 * Copyright © 2016 MangoExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Plugin\Paypal;

class Cart
{

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */   
    protected $helper;
    protected $lofadded;
    protected $quote;
    protected $cart;
        
    public function __construct(
        \Mango\Loworderfee\Helper\Data $helper,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->helper = $helper;
        $this->quote = $quote;
        $this->cart = $cart;
    }
    
    public function afterGetAmounts(\Magento\Paypal\Model\Cart $subject,
        $result) {

        if (!$this->lofadded) {
            $this->lofadded = true;
            $quote = $this->cart->getQuote();
            foreach ($quote->getAllAddresses() as $address) {
                $result[$subject::AMOUNT_SHIPPING] += $address->getLoworderfeeAmount();
            }
        }
        return $result;
    }
}
