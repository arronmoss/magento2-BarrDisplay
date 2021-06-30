<?php

namespace Mango\Loworderfee\Model\Config\Source;

class Taxclass implements \Magento\Framework\Option\ArrayInterface
{

    protected $taxClass;

    public function __construct(\Magento\Tax\Model\TaxClass\Source\Product $source)
    {
        $this->taxClass = $source;
    }

    public function toOptionArray()
    {
        $options = $this->taxClass->getAllOptions();
        return $options;
    }
}
