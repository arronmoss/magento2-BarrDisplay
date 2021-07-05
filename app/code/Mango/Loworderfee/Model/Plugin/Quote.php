<?php

/**
 * Copyright © 2016 MangoExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Plugin;

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

    public function aroundValidateMinimumAmount(
        \Magento\Quote\Model\Quote $_quote,
        \Closure $proceed,
        $multishipping = false,
        $checkMinimumAmount = false
    ) {

        if ($checkMinimumAmount) {
            return $proceed($multishipping, $checkMinimumAmount);
        }

        $websiteId = $_quote->getStore()->getWebsiteId();
        $minOrderActive = $this->helper->getWebsiteConfigValue(
            'active',
            $websiteId
        );
        if (!$minOrderActive) {
            return true;
        }
        $lowOrderFeeActive = $this->helper->getWebsiteConfigValue(
            'low_order_fee_active',
            $websiteId
        );
        
        if (!$lowOrderFeeActive) {
            return $proceed($multishipping, $checkMinimumAmount);
        }

        
        /* check if low order fee is active and applies to selected customer groups... */
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
            $group_id = $_quote->getCustomerGroupId();
            /* if quote customer group not in list, return true */
            if (!in_array($group_id, $customer_groups)) {
                return true;
            }
        }
        /* added to check shipping group validation */
        /* added to check shipping group validation */
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
            $_selected_shipping_method = $_quote->getShippingAddress()->getShippingMethod();
            /* if quote shipping method in list, return true */
            if (in_array($_selected_shipping_method, $shipping_methods)) {
                return true;
            }
        }
        if ($minOrderActive) {
            if ($lowOrderFeeActive) {
                return true;
            }
            return $proceed($multishipping, $checkMinimumAmount);
        }
        return true;
    }
}
