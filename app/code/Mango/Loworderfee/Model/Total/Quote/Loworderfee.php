<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Loworderfee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;
    protected $helper = null;
    protected $priceCurrency = null;
    protected $calculationTool;
    protected $logger;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Mango\Loworderfee\Helper\Data $helper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Model\Calculation $calculation,
        \Mango\Loworderfee\Helper\Logger $logger
    ) {
    
        $this->quoteValidator = $quoteValidator;
        $this->priceCurrency = $priceCurrency;
        $this->code = "loworderfee";
        $this->helper = $helper;
        $this->calculationTool = $calculation;
        $this->logger = $logger;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
    
        parent::collect($quote, $shippingAssignment, $total);
        $websiteId = $quote->getStore()->getWebsiteId();
        $_lof_active = $this->helper->getWebsiteConfigValue(
            'low_order_fee_active',
            $websiteId
        );
        $address = $this->_getAddress();
        
        /* restart values */
        $address->setLoworderfeeAmount(0)->setBaseLoworderfeeAmount(0);
        $total->setTotalAmount('loworderfee', 0)->setBaseTotalAmount('loworderfee', 0);
        
        /* don't calculate total for shipping/virtual or billing/not-virtual*/
        if ($quote->getIsVirtual()
            && $address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            return $this;
        } elseif (!$quote->getIsVirtual()
            && $address->getAddressType() != \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            return $this;
        }
        if (!$address->validateMinimumAmount() && $_lof_active) {
            $_ranges_enabled = $this->helper->getWebsiteConfigValue(
                'low_order_fee_ranges',
                $websiteId
            );

            if ($_ranges_enabled) {
                $_ranges_config = $this->getLowOrderFeeRanges($websiteId, $address);
                $_method = $_ranges_config[1];
                $_lof_value = $_ranges_config[2];
            } else {
                $_method = $this->helper->getWebsiteConfigValue(
                    'low_order_fee_method',
                    $websiteId
                );
                $_lof_value = $this->helper->getWebsiteConfigValue(
                    'low_order_fee',
                    $websiteId
                );
            }
            
            $_base_loworderfee = 0;
            switch ($_method) {
                case 'fixed':
                    $_base_loworderfee = $_lof_value;
                    break;
                case 'percentage':
                    $_percentage = $_lof_value;
                    $_base_loworderfee = ($total->getBaseSubtotalWithDiscount() * $_percentage / 100);
                    break;
                case 'difference':
                    $_base_loworderfee = $this->calculateDifferenceAmount($quote, $total);
                    break;
            }
            
            $_loworderfee = $this->priceCurrency->convert($_base_loworderfee);

            $total->setTotalAmount('loworderfee', $_loworderfee);
            $total->setBaseTotalAmount('loworderfee', $_base_loworderfee);
            /* skip this in version 2.2 */
            $magentoVersion = $this->helper->getMagentoVersion();
            if (version_compare($magentoVersion, '2.2', '<')) {
                $total->setGrandTotal($total->getGrandTotal() + $_loworderfee);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() + $_base_loworderfee);
            }
                
            $address->setLoworderfeeAmount($_loworderfee);
            $address->setBaseLoworderfeeAmount($_base_loworderfee);
        } else {/* no lof applies */
            $address->setLoworderfeeAmount(0);
            $address->setBaseLoworderfeeAmount(0);
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
    
        $amount = 0;
        $storeId = $quote->getStoreId();
        
        $amount = $total->getLoworderfeeAmount();
        
        if ($amount != 0) {
            $_title = $this->helper->getConfigValue(
                'low_order_fee_title',
                $storeId
            );
            
            return [
                'code' => "loworderfee",
                'title' => __($_title),
                'value' => $amount
            ];
        }
        return [];
    }

    private function calculateDifferenceAmount(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
    
        $websiteId = $quote->getStore()->getWebsiteId();
        $_amount = $this->helper->getWebsiteConfigValue(
            'amount',
            $websiteId
        );
        $_reference = $this->helper->getWebsiteConfigValue(
            'low_order_fee_reference',
            $websiteId
        );
        $_subtotal_to_compare = 0;
        switch ($_reference) {
            case \Mango\Loworderfee\Model\Config\Source\Reference::BASE_SUBTOTAL:
                $_subtotal_to_compare = $total->getBaseSubtotal();
                break;
            case \Mango\Loworderfee\Model\Config\Source\Reference::BASE_SUBTOTAL_WITH_DISCOUNT:
                $_subtotal_to_compare = $total->getBaseSubtotalWithDiscount();
                break;
            case \Mango\Loworderfee\Model\Config\Source\Reference::SUBTOTAL_INCL_TAX:
                $_subtotal_to_compare = $total->getSubtotalInclTax();
                break;
            case \Mango\Loworderfee\Model\Config\Source\Reference::SUBTOTAL_INCL_TAX_WITH_DISCOUNT:
                $_subtotal_to_compare = $total->getSubtotalInclTax() + $total->getDiscountAmount();
                break;
        }
        $_low_order_fee = $_amount - $_subtotal_to_compare;
        return $_low_order_fee;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        $_label = $this->helper->getConfigValue(
            'low_order_fee_title'
        );
        return $_label;
    }
    
    public function getLowOrderFeeRanges($websiteId, $address)
    {
        
        $_data = $this->helper->getWebsiteConfigValue(
            'low_order_fee_ranges_data',
            $websiteId
        );

        $taxInclude = $this->helper->getConfigValue('tax_including');
        $taxes = $taxInclude ? $address->getBaseTaxAmount() : 0;
        $amount = $address->getBaseSubtotalWithDiscount() + $taxes ;

        $_rows = explode("\n", $_data);
        
        /* structure: max-amount, method, lof-value */
        $_config = [ 0, false, 0 ];
        $_init_range_value = 0;
        foreach ($_rows as $_row) {
            $_config_data = str_getcsv(trim($_row));
            /* validate range */
            $_valid_row = $this->_validateRow($_config_data);
            if ($_valid_row && $amount > $_init_range_value && $amount <= $_config_data[0]) {
                $_config = $_config_data ;
                break;
            } else {
                $_init_range_value  = $_config_data[0]; /* use it for next range validation*/
            }
        }
        return $_config;
    }
    
    /* validate row data .. */
    protected function _validateRow($_config_data)
    {
        return count($_config_data)  == 3;
    }
}
