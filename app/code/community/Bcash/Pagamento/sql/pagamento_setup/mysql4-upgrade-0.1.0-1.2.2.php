<?php
/**
 * Fix old payment method name
 */
try {
    $installer = $this;
    $installer->startSetup();

    $table_order_payment = $installer->getTable('sales_flat_order_payment');
    $table_order = $installer->getTable('sales_flat_order');
    $table_quote_payment = $installer->getTable('sales_flat_quote_payment');
    $table_quote = $installer->getTable('sales_flat_quote');

    // Payment method codes
    $credit_card = "(1, 2, 37, 45, 55, 56, 63)";
    $bankslip = "10";
    $onlinetransfer = "(58, 59, 60, 61, 62)";

    $installer->run("
        UPDATE `{$table_order_payment}`
            SET `method` = 'bcash_creditcard'
            WHERE `parent_id` IN
                (SELECT `entity_id` FROM `{$table_order}`
                    WHERE `payment_method_bcash` IN {$credit_card}
                 );

        UPDATE `{$table_order_payment}`
            SET `method` = 'bcash_bankslip'
            WHERE `parent_id` IN
                (SELECT `entity_id` FROM `{$table_order}`
                    WHERE `payment_method_bcash` = {$bankslip}
                 );

        UPDATE `{$table_order_payment}`
            SET `method` = 'bcash_onlinetransfer'
            WHERE `parent_id` IN
                (SELECT `entity_id` FROM `{$table_order}`
                    WHERE `payment_method_bcash` IN {$onlinetransfer}
                 );

        UPDATE `{$table_quote_payment}`
            SET `method` = 'bcash_creditcard'
            WHERE `quote_id` IN
                (SELECT `entity_id` FROM `{$table_quote}`
                    WHERE `payment_method_bcash` IN {$credit_card}
                 );

        UPDATE `{$table_quote_payment}`
            SET `method` = 'bcash_bankslip'
            WHERE `quote_id` IN
                (SELECT `entity_id` FROM `{$table_quote}`
                    WHERE `payment_method_bcash` = {$bankslip}
                 );

        UPDATE `{$table_quote_payment}`
            SET `method` = 'bcash_onlinetransfer'
            WHERE `quote_id` IN
                (SELECT `entity_id` FROM `{$table_quote}`
                    WHERE `payment_method_bcash` IN {$onlinetransfer}
                 );
    ");

    $installer->endSetup();

} catch (Exception $e) {
    Mage::log("Exception - app/code/community/Bcash/Pagamento/sql/pagamento_setup/mysql4-upgrade-0.1.0-1.2.2.php: " . $e->getMessage());
}