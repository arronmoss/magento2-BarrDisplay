<?php

/**
 * Copyright © 2016 MangoExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Plugin\Quote;

class TotalsCollector
{

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $helper;

    public function __construct(
        \Mango\Loworderfee\Helper\Data $helper
    ) {

        $this->helper = $helper;
    }

    public function aroundCollect(
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote
    ) {
        
        $loworderfeeEnabled = $this->helper->getWebsiteConfigValue(
            'low_order_fee_active',
            false
        );

        if(!$loworderfeeEnabled){
            return $proceed($quote);
        }
        
        $total = $proceed($quote);
        
        $quote->setLoworderfeeAmount(0);
        $quote->setBaseLoworderfeeAmount(0);
        $quote->setLofTaxAmount(0);
        $quote->setBaseLofTaxAmount(0);
        foreach ($quote->getAllAddresses() as $address) {
            $quote->setLoworderfeeAmount($quote->getLoworderfeeAmount()+$address->getLoworderfeeAmount());
            $quote->setBaseLoworderfeeAmount($quote->getBaseLoworderfeeAmount()+$address->getBaseLoworderfeeAmount());
            $quote->setLofTaxAmount($quote->getLofTaxAmount() + $address->getLofTaxAmount());
            $quote->setBaseLofTaxAmount($quote->getBaseLofTaxAmount()+$address->getBaseLofTaxAmount());
        }
        
        return $total;
    }
}
