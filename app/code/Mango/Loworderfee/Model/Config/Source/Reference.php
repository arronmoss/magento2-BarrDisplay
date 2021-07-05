<?php

namespace Mango\Loworderfee\Model\Config\Source;

class Reference implements \Magento\Framework\Option\ArrayInterface
{

    const BASE_SUBTOTAL_WITH_DISCOUNT = "BaseSubtotalWithDiscount";
    const BASE_SUBTOTAL = "BaseSubtotal";
    const SUBTOTAL_INCL_TAX = "SubtotalInclTax";
    const SUBTOTAL_INCL_TAX_WITH_DISCOUNT = "SubtotalInclTaxWithDiscount";

    public function toOptionArray()
    {
        $options = [];
        $options[] = ['value' =>  $this::BASE_SUBTOTAL_WITH_DISCOUNT ,
            'label' => __('Base Subtotal With Discount (Magento Default)')];
        $options[] = ['value' => $this::BASE_SUBTOTAL,
            'label' => __('Base Subtotal')];
        $options[] = ['value' => $this::SUBTOTAL_INCL_TAX,
            'label' => __('Subtotal Incl. Tax')];
        $options[] = ['value' => $this::SUBTOTAL_INCL_TAX_WITH_DISCOUNT,
            'label' => __('Subtotal Incl. Tax With Discount')];
        return $options;
    }
}
