<?php

namespace Mango\Loworderfee\Model\Total\Creditmemo;

class Loworderfee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{

    protected $logger;
    protected $scopeConfig;

    public function __construct(
        \Mango\Loworderfee\Helper\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    
        parent::__construct();
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
    
        $order = $creditmemo->getOrder();
        $baseCmTotal = $creditmemo->getBaseGrandTotal();
        $creditmemoTotal = $creditmemo->getGrandTotal();
        $baseCmTaxTotal = $creditmemo->getBaseTaxAmount();
        $creditmemoTaxTotal = $creditmemo->getBaseTaxAmount();
        $store = false;
        if ($creditmemo->getInvoice()) {
            $invoice = $creditmemo->getInvoice();
            $baseLofToCredit = $invoice->getBaseLoworderfeeAmount();
            $loworderfeeToCredit = $invoice->getLoworderfeeAmount();
            $lofTaxAmountToCredit = $invoice->getLofTaxAmount();
            $baseLofTaxAmountToCredit = $invoice->getBaseLofTaxAmount();
            $store = $invoice->getStore();
        } else {
            $baseLofToCredit = $order->getBaseLofInvoiced();
            $loworderfeeToCredit = $order->getLofInvoiced();
            $lofTaxAmountToCredit = $order->getLofTaxAmountInvoiced();
            $baseLofTaxAmountToCredit = $order->getBaseLofTaxAmountInvoiced();
            $store = $order->getStore();
        }
        if (!$baseLofToCredit > 0) {
            return $this;
        }
        $baseCmGrandTotal = $baseCmTotal + $baseLofToCredit;
        $cmGrandTotal = $creditmemoTotal + $loworderfeeToCredit;
        $_lof_includes_tax = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/low_order_fee_tax_includes_tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        if (!$_lof_includes_tax) { /* will add lof tax to the total */
            $baseCmGrandTotal+=$baseLofTaxAmountToCredit;
            $cmGrandTotal+= $lofTaxAmountToCredit;
        }
        // Adding invoiced Low Order Fee from Credit memo total
        $creditmemo->setBaseGrandTotal($baseCmGrandTotal);
        $creditmemo->setGrandTotal($cmGrandTotal);
        $creditmemo->setBaseLoworderfeeAmount($baseLofToCredit);
        $creditmemo->setLoworderfeeAmount($loworderfeeToCredit);
        $creditmemo->setBaseTaxAmount($baseCmTaxTotal + $baseLofTaxAmountToCredit);
        $creditmemo->setTaxAmount($creditmemoTaxTotal + $lofTaxAmountToCredit);
        $creditmemo->setBaseLofTaxAmount($baseLofTaxAmountToCredit);
        $creditmemo->setLofTaxAmount($lofTaxAmountToCredit);
        /* updating order */
        $order->setBaseLofAmountRefunded($order->getBaseLofAmountRefunded() + $baseLofToCredit);
        $order->setLofAmountRefunded($order->getLofAmountRefunded() + $loworderfeeToCredit);
        $order->setBaseLofTaxAmountRefunded($order->getBaseLofTaxAmountRefunded() + $baseLofTaxAmountToCredit);
        $order->setLofTaxAmountRefunded($order->getLofTaxAmountRefunded() + $lofTaxAmountToCredit);
        return $this;
    }
}
