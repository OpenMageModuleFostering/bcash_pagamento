<?php

try {

    $installer = $this;
    $installer->startSetup();

    $installer->addAttribute(
        'quote',  /* order, quote, order_item, quote_item */
        'transaction_id_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'quote',  /* order, quote, order_item, quote_item */
        'status_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => false, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'quote',  /* order, quote, order_item, quote_item */
        'description_status_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => false, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'quote',  /* order, quote, order_item, quote_item */
        'payment_link_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'quote',  /* order, quote, order_item, quote_item */
        'installments_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'quote',  /* order, quote, order_item, quote_item */
        'payment_method_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'order',  /* order, quote, order_item, quote_item */
        'transaction_id_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'order',  /* order, quote, order_item, quote_item */
        'status_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => false, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'order',  /* order, quote, order_item, quote_item */
        'description_status_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => false, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'order',  /* order, quote, order_item, quote_item */
        'payment_link_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'order',  /* order, quote, order_item, quote_item */
        'installments_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->addAttribute(
        'order',  /* order, quote, order_item, quote_item */
        'payment_method_bcash',
        array(
            'type' => 'varchar', /* int, varchar, text, decimal, datetime */
            'nullable' => true, /* default true */
            'grid' => true, /* or true if you wan't use this attribute on orders grid page */
        )
    );

    $installer->endSetup();

} catch (Exception $e) {

    Mage::log($e->getMessage());

}