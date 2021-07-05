<?php

namespace Mango\Loworderfee\Helper;

class Configuration
{

    protected $scopeConfig;
    protected $store;
    protected $context;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->context = $context;
    }

    public function getStoreId()
    {
        if (!$this->store) {
            $this->store = $this->context->getStoreManager()->getStore();
        }
        return $this->store->getId();
    }
    
    public function getScopeConfigValue($_value)
    {
        if (!$this->scopeConfig) {
            $this->scopeConfig = $this->context->getScopeConfig();
        }
        return $this->scopeConfig->getValue(
            $_value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    
    public function getDescription()
    {
        return $this->getScopeConfigValue('sales/minimum_order/low_order_fee_details');
    }

    public function getTitle()
    {
        return $this->getScopeConfigValue('sales/minimum_order/low_order_fee_title');
    }
}
