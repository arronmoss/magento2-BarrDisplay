<?php

/**
 * Copyright © 2016 MangoExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Plugin\Quote;

class Address
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

    public function aroundValidateMinimumAmount(
        \Magento\Quote\Model\Quote\Address $_address,
        \Closure $proceed
    ) {
        $websiteId = $_address->getQuote()->getStore()->getWebsiteId();
        $validateEnabled = $this->helper->getWebsiteConfigValue(
            'active',
            $websiteId
        );
        
        if (!$validateEnabled) {
            return true;
        }
        
        $loworderfeeEnabled = $this->helper->getWebsiteConfigValue(
            'low_order_fee_active',
            $websiteId
        );
        if (!$loworderfeeEnabled) {
            return $proceed();
        }
        
        $skipCustomerGroup = $this->_skipCurrentCustomerGroup($_address, $websiteId);
        if ($skipCustomerGroup) {
            return true;
        }
        
        /* added to check shipping group validation */
        if ($_address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            /* check if customer validation is enabled */
            $shipping_method_validate = $this->helper->getWebsiteConfigValue(
                'low_order_fee_shipping_method_disable',
                $websiteId
            );
            if ($shipping_method_validate) {
                $shipping_methods = $this->helper->getWebsiteConfigValue(
                    'low_order_fee_shipping_method',
                    $websiteId
                );
                $shipping_methods = explode(",", $shipping_methods);
                /* get quote shipping method */
                $_selected_shipping_method = $_address->getShippingMethod();
                /* if quote shipping method in list, return true */
                if (in_array($_selected_shipping_method, $shipping_methods)) {
                    return true;
                }
            }
        }

        if ($_address->getQuote()->getIsVirtual() &&
            $_address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            return true;
        } elseif (!$_address->getQuote()->getIsVirtual() &&
            $_address->getAddressType() != \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            return true;
        }
        $amount = $this->helper->getWebsiteConfigValue(
            'amount',
            $websiteId
        );
        /*$taxInclude = $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );*/

        $_reference = $this->helper->getWebsiteConfigValue(
            'low_order_fee_reference',
            $websiteId
        );

        $_amount_to_compare = $this->_getAmountToCompare($_reference, $_address);
        
        if ($_amount_to_compare < $amount) {
            return false;
        }
        return true;
    }
    
    protected function _skipCurrentCustomerGroup($_address, $websiteId)
    {
        /* added to check customer groups */
        /* check if customer validation is enabled */
        $customer_group_validate = $this->helper->getWebsiteConfigValue(
            'low_order_fee_customer_group_enable',
            $websiteId
        );
        
        /* get customer groups */
        if ($customer_group_validate) {
            $customer_groups = $this->helper->getWebsiteConfigValue(
                'low_order_fee_customer_group',
                $websiteId
            );
            $customer_groups = explode(",", $customer_groups);
            /* get quote customer group */
            $group_id = (int) $_address->getQuote()->getCustomerGroupId();
            /* if quote customer group not in list, return true */
            if (!in_array($group_id, $customer_groups)) {
                return true;
            }
        }
        return false;
    }
    
    protected function _getAmountToCompare($_reference, $_address)
    {
        $_amount_to_compare = 0;
        switch ($_reference) {
            case \Mango\Loworderfee\Model\Config\Source\Reference::BASE_SUBTOTAL:
                $_amount_to_compare = $_address->getBaseSubtotal();
                break;
            case \Mango\Loworderfee\Model\Config\Source\Reference::SUBTOTAL_INCL_TAX:
                $_amount_to_compare = $_address->getSubtotalInclTax();
                break;
            case \Mango\Loworderfee\Model\Config\Source\Reference::SUBTOTAL_INCL_TAX_WITH_DISCOUNT:
                $_amount_to_compare = $_address->getSubtotalInclTax() + $_address->getDiscountAmount();
                break;
            case \Mango\Loworderfee\Model\Config\Source\Reference::BASE_SUBTOTAL_WITH_DISCOUNT:
            default:
                $_amount_to_compare = $_address->getBaseSubtotalWithDiscount();
                break;
        }
        return $_amount_to_compare;
    }
}
