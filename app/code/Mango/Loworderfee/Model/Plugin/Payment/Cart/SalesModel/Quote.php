<?php

/**
 * Copyright © 2016 MangoExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Plugin\Payment\Cart\SalesModel;

class Quote
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
    
    public function aroundGetLoworderfeeAmount(
        \Magento\Payment\Model\Cart\SalesModel\Quote $_quote,
        \Closure $proceed
    ) {
                
        return $_quote->getTaxContainer()->getLoworderfeeAmount();
    }
}
