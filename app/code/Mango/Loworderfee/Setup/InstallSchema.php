<?php

namespace Mango\Loworderfee\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /* list of available fields */
        /* all decimal , as all prices */
        $_fields_array = [
            'quote' => [
                'loworderfee_amount',
                'base_loworderfee_amount',
                'lof_tax_amount',
                'base_lof_tax_amount'],
            'quote_address' => [
                'loworderfee_amount',
                'base_loworderfee_amount',
                'lof_tax_amount',
                'base_lof_tax_amount'
            ],
            'sales_order' => [
                'loworderfee_amount', 'base_loworderfee_amount',
                'lof_tax_amount', 'base_lof_tax_amount',
                'lof_invoiced', 'base_lof_invoiced',
                'lof_tax_amount_invoiced', 'base_lof_tax_amount_invoiced',
                'lof_amount_refunded', 'base_lof_amount_refunded',
                'lof_tax_amount_refunded', 'base_lof_tax_amount_refunded',
                'lof_tax_amount_canceled', 'base_lof_tax_amount_canceled'
            ],
            'sales_invoice' => [
                'loworderfee_amount',
                'base_loworderfee_amount',
                'lof_tax_amount',
                'base_lof_tax_amount'
            ],
            'sales_creditmemo' => [
                'loworderfee_amount',
                'base_loworderfee_amount',
                'lof_tax_amount',
                'base_lof_tax_amount'
            ]
        ];
        $installer = $setup;
        $connection = $installer->getConnection();
        $installer->startSetup();
        foreach ($_fields_array as $_table => $_fields) {
            foreach ($_fields as $_new_field) {
                $column = [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'comment' => $_new_field,
                    'default' => '0'
                ];
                $connection->addColumn($setup->getTable($_table), $_new_field, $column);
            }
        }
        $installer->endSetup();
    }
}
