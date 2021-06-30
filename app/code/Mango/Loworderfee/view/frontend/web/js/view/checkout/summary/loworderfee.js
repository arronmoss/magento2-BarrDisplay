define(
    [
            'Magento_Checkout/js/view/summary/abstract-total',
            'Magento_Checkout/js/model/quote',
            'Magento_Catalog/js/price-utils',
            'Magento_Checkout/js/model/totals'
        ],
    function (Component, quote, priceUtils, totals) {
            "use strict";
            return Component.extend(
                {
                defaults: {
                    isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                    template: 'Mango_Loworderfee/checkout/summary/loworderfee'
                },
                totals: quote.getTotals(),
                isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
                isDisplayed: function () {
                    
                    return this.isFullMode() && totals.getSegment('loworderfee') !== null && totals.getSegment('loworderfee').value > 0;
                },
                getValue: function () {
                    var price = 0;
                    if (this.totals() && totals.getSegment('loworderfee') !== null) {
                        price = totals.getSegment('loworderfee').value;
                    }

                    return this.getFormattedPrice(price);
                },
                getBaseValue: function () {
                    var price = 0;
                    if (this.totals()) {
                        price = this.totals().base_fee;
                    }

                    return priceUtils.formatPrice(price, quote.getBasePriceFormat());
                }
                }
            );
        }
);