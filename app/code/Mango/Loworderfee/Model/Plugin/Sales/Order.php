<?php

namespace Mango\Loworderfee\Model\Plugin\Sales;

class Order
{

    #protected $checkoutSession;
    protected $cart;
    protected $backendSessionQuote;
    protected $logger;

    protected $registry;
    const AMOUNT_PAYMENT = 'payment_fee';
    const AMOUNT_SUBTOTAL = 'subtotal';
        
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Mango\Loworderfee\Helper\Logger $logger,
        \Magento\Framework\Registry $registry
    ) {
        $this->cart = $cart;
        $this->backendSessionQuote = $sessionQuote;
        $this->logger = $logger;
        $this->registry = $registry;
    }

    public function afterPlace(
        \Magento\Sales\Model\Order $order,
        $result
    ) {
    
        $quote = $this->cart->getQuote();
        if (!$quote->getId()) {
            $quote = $this->backendSessionQuote->getQuote();
        }
        $order->setLoworderfeeAmount($quote->getLoworderfeeAmount());
        $order->setBaseLoworderfeeAmount($quote->getBaseLoworderfeeAmount());
        $order->setLofTaxAmount($quote->getLofTaxAmount());
        $order->setBaseLofTaxAmount($quote->getBaseLofTaxAmount());
        $order->save();
        return $result;
    }
}
