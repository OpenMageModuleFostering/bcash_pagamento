<?php

/**
 * Class Bcash_Pagamento_Model_Observer
 */
class Bcash_Pagamento_Model_Observer
{
    /**
     * Método para verificar alteração de status do pedido
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkOrderState(Varien_Event_Observer $observer)
    {
        $origOrderData = $observer->getEvent()->getData('data_object')->getOrigData();
        $newOrderData = $observer->getEvent()->getData('data_object')->getData();
        if (($origOrderData['state'] !== $newOrderData['state']) && ($newOrderData['state'] == Mage_Sales_Model_Order::STATE_CANCELED)) {
            // Verifica se pedido possui transação Bcash relacionada
            $order = $observer->getOrder()->getData();
            if(!empty($order['transaction_id_bcash']) && !is_null($order['transaction_id_bcash'])) {
                $orderId = $order['entity_id'];
                $order = Mage::getModel('sales/order')->load($orderId);
                $order->addStatusHistoryComment('Pedido não cancelado através do cancelamento Bcash. A transação ' . $order['transaction_id_bcash'] . ' não foi alterada.');
                $order->save();
            }
        }
    }

    /**
     * Registra os dados do pedido na sessão.
     * See etc/config.xml
     * Triggered by: Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$this->getQuote()));
     * @param Varien_Event_Observer $observer
     */
    public function saveOrderQuoteToSession($observer)
    {
        /* @var $event Varien_Event */
        $event = $observer->getEvent();
        /* @var $order Mage_Sales_Model_Order */
        $order = $event->getOrder();
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $event->getQuote();
        $session = Mage::getSingleton('checkout/session');
        $quoteId = $quote->getId();
        $orderId = $order->getId();
        $incrId = $order->getIncrementId();
        Mage::log("Saving quote  [$quoteId] and order [$incrId] to checkout/session");
        $session->setData('OrderIdBcash', $orderId);
        $session->setData('OrderIncrementIdBcash', $incrId);
        $session->setData('QuoteIdBcash', $quoteId);
        unset($event);
        unset($order);
        unset($quote);
        unset($session);
        return $this;
    }

    /*
    <global>
    - If you want your observer to listen no matter where the event is dispatched from, put it here. You can also put it in "frontend" or "adminhtml".
    <events>
    - This is the element that stores all of the events that are registered.
    <checkout_submit_all_after>
    - This is the "event" that you are listening to.
    <observers>
    - This is the type of event. I don't think there are others.
    <awesome_example>
    - This is a unique string that defines this configuration. It can be anything, and just needs to be unique.
    <type>
    - I have always used singleton, but other options can be "model" or "object". The "singleton" will create the object as Mage::getSingleton()
    while both "object" and "model" will use Mage::getModel() when creating the observer object.
    <class>
    - This is the observer class.
    <method>
    - This is the function to be called in the observer class.
    */

    /**
     * Adiciona o Link do meio de pagamento a página de sucesso.
     * @param $observer
     */
    public function orderSuccessEvent($observer)
    {
        Mage::log("Bcash_Pagamento_Model_Observer::showPaymentLink");
        try {
            $order = new Mage_Sales_Model_Order();
            $lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order->loadByIncrementId($lastOrderId);
            $quoteId = $order->getQuoteId();
            $quote = Mage::getModel("sales/quote")->loadByIdWithoutStore($quoteId);
            $type = null;
            $payment_method_bcash = $quote->getPaymentMethodBcash();
            if ($payment_method_bcash) {
                $helper = new Bcash_Pagamento_Helper_PaymentMethod();
                $type = $helper->getPaymentMethod($payment_method_bcash);
            }
            $layout = Mage::app()->getLayout();
            $block = $layout->createBlock(
                'Mage_Core_Block_Template',
                'link_pagamento_bcash',
                array('template' => 'pagamento/checkout/success.phtml')
            );
            $block->setOrder($order);
            $block->setQuote($quote);
            $block->setType($type);
            $layout->getBlock('content')->append($block);
        } catch(Exception $e) {
            Mage::log($e->getMessage());
        }
    }

}