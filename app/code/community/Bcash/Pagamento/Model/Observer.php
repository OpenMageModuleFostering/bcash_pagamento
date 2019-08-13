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
        // @var $event Varien_Event
        $event = $observer->getEvent();
        // @var $order Mage_Sales_Model_Order
        $order = $event->getOrder();
        // @var $quote Mage_Sales_Model_Quote
        $quote = $event->getQuote();


        $quoteId = $quote->getId();
        $orderId = $order->getId();
        $incrId = $order->getIncrementId();

        // Sync datas
        Mage::helper('pagamento')->updateOrderSyncBcashDataWithQuote($orderId, $quoteId);

        // Session values
        $session = Mage::getSingleton('checkout/session');
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

    public function updateSalesQuotePayment(Varien_Event_Observer $observer)
    {
        try {
            if (!is_null($observer->getQuote())) {
                $params = Mage::app()->getFrontController()->getRequest()->getParams();
                $params['installments_bcash'] = isset($params['installments_bcash']) ? $params['installments_bcash'] : 1;

                //Adiciona Desconto ao Pedido caso 1x Credito, Boleto ou TEF (configurados no Backend)
                $discountAmount = 0;
                if ($params['installments_bcash'] == 1) {
                    $transaction = new Bcash_Pagamento_Helper_Transaction();
                    $discountAmount = $transaction->calculateDiscount($params['payment-method']);
                }

                $quote = $observer->getQuote();
                $quote->collectTotals();

                if($discountAmount > 0) {
                    $grandTotal = $quote->getGrandTotal();
                    $subTotalWithDiscount = $quote->getSubtotalWithDiscount();
                    $baseGrandTotal = $quote->getBaseGrandTotal();
                    $baseSubTotalWithDiscount = $quote->getBaseSubtotalWithDiscount();

                    // Outros descontos aplicados
                    $objDiscountAmount = $quote->getDiscountAmount();
                    if ($objDiscountAmount <> 0) {
                        $discountAmount = (-1 * ((-$discountAmount) + $objDiscountAmount));
                        $grandTotal = $grandTotal + (-1 * $objDiscountAmount);
                        $subTotalWithDiscount = $subTotalWithDiscount + (-1 * $objDiscountAmount);
                        $baseGrandTotal = $baseGrandTotal + (-1 * $objDiscountAmount);
                        $baseSubTotalWithDiscount = $baseSubTotalWithDiscount + (-1 * $objDiscountAmount);
                    }

                    $totalDiscountAmount = $discountAmount;
                    $subtotalWithDiscount = $subTotalWithDiscount - $discountAmount;
                    $baseTotalDiscountAmount = $discountAmount;
                    $baseSubtotalWithDiscount = $baseSubTotalWithDiscount - $discountAmount;

                    $quote->setDiscountAmount(-$totalDiscountAmount);
                    $quote->setSubtotalWithDiscount($subtotalWithDiscount);
                    $quote->setBaseDiscountAmount($baseTotalDiscountAmount);
                    $quote->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
                    $quote->setGrandTotal($grandTotal - $totalDiscountAmount);
                    $quote->setBaseGrandTotal($baseGrandTotal - $baseTotalDiscountAmount);
                    $quote->setTotalsCollectedFlag(false)->collectTotals();
                    $quote->save();
                }
            }
        } catch (Exception $e) { Mage::log($e->getMessage()); }
    }

}