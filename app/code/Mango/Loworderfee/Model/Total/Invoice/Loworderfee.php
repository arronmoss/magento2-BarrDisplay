<?php

/**
 * Copyright © 2016 Mangoextensions.com. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Total\Invoice;

class Loworderfee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{

    protected $logger;
    protected $scopeConfig;
    protected $checkoutSession;
    
    public function __construct(
        \Mango\Loworderfee\Helper\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
    
        parent::__construct();
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
       
        if (!$order->getId()) {
            /* for paypal method, the order was not saved yet, use quote data... */
            $order = $this->checkoutSession->getQuote();
        }
        
        if (!$order->getLoworderfeeAmount()) {
            return $this;
        }
        
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getLoworderfeeAmount() && !$previusInvoice->isCancelled()) {
                $includeLofTax = false;
            }
        }
        $baseLoworderfee = $order->getBaseLoworderfeeAmount();
        $baseLofInvoiced = $order->getBaseLofInvoiced();
        $baseInvoiceTotal = $invoice->getBaseGrandTotal();
        $baseInvoiceTaxAmount = $invoice->getBaseTaxAmount();
        $loworderfee = $order->getLoworderfeeAmount();
        $loworderfeeInvoiced = $order->getLofInvoiced();
        $invoiceTotal = $invoice->getGrandTotal();
        $invoiceTaxAmount = $invoice->getTaxAmount();
        $lofTaxAmount = $order->getLofTaxAmount();
        $baseLofTaxAmount = $order->getBaseLofTaxAmount();

        $lofTaxAmountInvoiced = $order->getLofTaxAmountInvoiced();
        $baseLofTaxAmountInvoiced = $order->getBaseLofTaxAmountInvoiced();

        if (!$baseLoworderfee || $baseLofInvoiced == $baseLoworderfee) {
            return $this;
        }
        
        $baseLofToInvoice = $baseLoworderfee - $baseLofInvoiced;
        $loworderfeeToInvoice = $loworderfee - $loworderfeeInvoiced;
        $baseLofTaxAmountToInvoice = $baseLofTaxAmount - $baseLofTaxAmountInvoiced;
        $lofTaxAmountToInvoice = $lofTaxAmount - $lofTaxAmountInvoiced;
        $baseInvoiceTotal = $baseInvoiceTotal + $baseLofToInvoice;
        $invoiceTotal = $invoiceTotal + $loworderfeeToInvoice;
        $store = $order->getStore();
        $_lof_includes_tax = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/low_order_fee_tax_includes_tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        if (!$_lof_includes_tax) { /* will add lof tax to the total */
            #$baseInvoiceTotal+= $baseLofTaxAmountToInvoice;
            #$invoiceTotal+= $lofTaxAmountToInvoice;
            $baseInvoiceTotal= $baseInvoiceTotal - $baseLofTaxAmount + $baseLofTaxAmountToInvoice;
            $invoiceTotal= $invoiceTotal -  $lofTaxAmount + $lofTaxAmountToInvoice;    
        }
        if ($invoice->isLast()) {
            /* needs to remove the lof-tax from the subtotal
             *  ( added in Mage_Sales_Model_Order_Invoice_Total_Subtotal) */
            $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() - $lofTaxAmountToInvoice);
            $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() - $baseLofTaxAmountToInvoice);
        }

        $invoice->setBaseGrandTotal($baseInvoiceTotal);
        $invoice->setGrandTotal($invoiceTotal);
        $invoice->setBaseTaxAmount($baseInvoiceTaxAmount + $baseLofTaxAmountToInvoice);
        $invoice->setTaxAmount($invoiceTaxAmount + $lofTaxAmountToInvoice);
        $invoice->setBaseLoworderfeeAmount($baseLofToInvoice);
        $invoice->setLoworderfeeAmount($loworderfeeToInvoice);
        $invoice->setLofTaxAmount($lofTaxAmountInvoiced + $lofTaxAmountToInvoice);
        $invoice->setBaseLofTaxAmount($baseLofTaxAmountInvoiced + $baseLofTaxAmountToInvoice);
        
        $order = $invoice->getOrder();
        
        $order->setBaseLofInvoiced($baseLofInvoiced + $baseLofToInvoice);
        $order->setLofInvoiced($loworderfeeInvoiced + $loworderfeeToInvoice);
        $order->setBaseLofTaxAmountInvoiced($baseLofTaxAmountInvoiced + $baseLofTaxAmountToInvoice);
        $order->setLofTaxAmountInvoiced($lofTaxAmountInvoiced + $lofTaxAmountToInvoice);

        return $this;
    }
}
