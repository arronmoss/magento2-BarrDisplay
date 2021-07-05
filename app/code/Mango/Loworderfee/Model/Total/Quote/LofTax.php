<?php

namespace Mango\Loworderfee\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class LofTax extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * Collect grand total address amount
     *
     * @param  \Magento\Quote\Model\Quote $quote
     * @param  \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param  \Magento\Quote\Model\Quote\Address\Total $total
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
        $this->code = "lof_tax";
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
        $address = $this->_getAddress();

        if ($quote->getIsVirtual()
            && $address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            return $this;
        } elseif (!$quote->getIsVirtual()
            && $address->getAddressType() != \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            return $this;
        }
        
        if (!$total->getLoworderfeeAmount()) {/* no lof applies */
            $address->setLofTaxAmount(0);
            $address->setBaseLofTaxAmount(0);
            return $this;
        }
        
        $total->setLofTaxAmount(0);
        $total->setBaseLofTaxAmount(0);

        $lofTaxClass = $this->helper->getWebsiteConfigValue(
            'low_order_fee_tax_class',
            $websiteId
        );
        if ($lofTaxClass) {
            $_lof_includes_tax = $this->helper->getWebsiteConfigValue(
                'low_order_fee_tax_includes_class',
                $websiteId
            );
            $store = $quote->getStore();
            $custTaxClassId = $quote->getCustomerTaxClassId();
            $taxCalculationModel = $this->calculationTool;
            /* @var $taxCalculationModel Mage_Tax_Model_Calculation */
            $request = $taxCalculationModel->getRateRequest(
                $quote->getShippingAddress(),
                $quote->getBillingAddress(),
                $custTaxClassId,
                $store
            );
            $rate = $taxCalculationModel->getRate($request->setProductClassId($lofTaxClass));
            if ($rate) {
                if (!$_lof_includes_tax) {
                    $lofTax = $total->getLoworderfeeAmount() * $rate / 100;
                    $lofBaseTax = $total->getBaseLoworderfeeAmount() * $rate / 100;
                } else {
                    $lofTax = $total->getLoworderfeeAmount() * $rate / (100 + $rate);
                    $lofBaseTax = $total->getBaseLoworderfeeAmount() * $rate / (100 + $rate);
                }
                $lofTax = round($lofTax, 4);
                $lofBaseTax = round($lofBaseTax, 4);
                $total->addTotalAmount('tax', $lofTax);
                $total->addBaseTotalAmount('tax', $lofBaseTax);
                $total->setLofTaxAmount($lofTax);
                $total->setBaseLofTaxAmount($lofBaseTax);
                $this->saveAppliedTaxes(
                    $total,
                    $taxCalculationModel->getAppliedRates($request),
                    $lofTax,
                    $lofBaseTax,
                    $rate
                );
                if (!$_lof_includes_tax) {
                    $total->setGrandTotal($total->getGrandTotal() + $lofTax);
                    $total->setBaseGrandTotal($total->getBaseGrandTotal() + $lofBaseTax);
                }
                
                $address->setLofTaxAmount($total->getLofTaxAmount());
                $address->setBaseLofTaxAmount($total->getBaseLofTaxAmount());
            }
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
     * @param                                         \Magento\Quote\Model\Quote $quote
     * @param                                         Address\Total              $total
     * @return                                        array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $amount = 0;
        $storeId = $quote->getStoreId();
        foreach ($quote->getAllAddresses() as $address) {
            $amount+=$address->getLofAmount();
        }
        if ($amount != 0) {
            $amount = $this->priceCurrency->convert($amount);

            $_title = $this->helper->getConfigValue(
                'low_order_fee_title',
                $storeId
            );
            return [
                'code' => "lof_tax",
                'title' => $_title,
                'value' => $amount
            ];
        }
        return [];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Low Order Fee Tax');
    }

    public function saveAppliedTaxes(
        \Magento\Quote\Model\Quote\Address\Total $address,
        $applied,
        $amount,
        $baseAmount,
        $rate
    ) {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = is_array($previouslyAppliedTaxes)?count($previouslyAppliedTaxes):0;
        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }
            
            if ($row['percent']!== null) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;
                $appliedAmount = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }
            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }
}
