<?php

namespace Mango\Loworderfee\Model\Config\Source;

class Method implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_array[] = ['value' => 'percentage', 'label' => __('Percentage')];
        $_array[] = ['value' => 'fixed', 'label' => __('Fixed')];
        $_array[] = ['value' => 'difference', 'label' => __('Difference')];
        return $_array;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $_array[] = ['percentage' => __('Percentage')];
        $_array[] = ['fixed' => __('Fixed')];
        $_array[] = ['difference' => __('Difference')];
        return $_array;
    }
}
