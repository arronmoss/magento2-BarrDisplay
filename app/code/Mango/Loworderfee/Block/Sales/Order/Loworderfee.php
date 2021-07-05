<?php

/**
 * Totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */

namespace Mango\Loworderfee\Block\Sales\Order;

class Loworderfee extends \Magento\Framework\View\Element\Template
{
    
    /**
     * @var Order
     */
    protected $order;
    
    protected $helper;

    protected $dataObjectFactory;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mango\Loworderfee\Helper\Data $helper,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        $data = []
    ) {
        $this->helper = $helper;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->source;
    }

    public function getStore()
    {
        return $this->order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();
        
        if ($this->source->getLoworderfeeAmount() > 0) {
            $storeId = $this->order->getStore()->getId();
            $_label = $this->helper->getConfigValue(
                'low_order_fee_title',
                $storeId
            );

            $loworderfee = $this->dataObjectFactory->create(
                [
                'code' => 'loworderfee',
                'strong' => false,
                'value' => $this->source->getLoworderfeeAmount(),
                'label' => __($_label)
                ]
            );

            if ($this->getBeforeCondition()) {
                $this->getParentBlock()->addTotalBefore($loworderfee, $this->getBeforeCondition());
            } else {
                $this->getParentBlock()->addTotal($loworderfee, $this->getAfterCondition());
            }
        }
        return $this;
    }
}
