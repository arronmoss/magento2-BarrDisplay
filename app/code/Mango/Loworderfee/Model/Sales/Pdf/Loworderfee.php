<?php

/**
 * Copyright © 2016 Mangoextensions.com. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mango\Loworderfee\Model\Sales\Pdf;

class Loworderfee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    public function getTotalsForDisplay()
    {
        $amount = $this->getAmount();
        if (!$amount) {
            return [];
        }

        // Display total amount
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $totals = [
            [
                'amount' => $this->getOrder()->formatPriceTxt($amount),
                'label' => __($this->getTitle()) . ':',
                'font_size' => $fontSize,
            ],
        ];
        return $totals;
    }
}
