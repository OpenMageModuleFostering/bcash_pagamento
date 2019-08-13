<?php

/**
 * Class Bcash_Pagamento_PaymentController
 */
class Bcash_Pagamento_PaymentController extends Mage_Core_Controller_Front_Action
{

    /**
     * Adiciona template.
     */
    public function redirectAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'paymentmethod', array('template' => 'pagamento/redirect.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    /**
     * Seta o redirecionamento da transação.
     */
    public function responseAction()
    {
        if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId")) {
            $orderId = $this->getRequest()->get("orderId");
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success.');
            $order->save();

            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure' => false));
        } else {
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure' => false));
        }
    }

    /**
     * Salva dados da transação na Sessão.
     */
    public function dadosAction()
    {
        $pid = Mage::app()->getRequest()->getPost('pid');
        $input = Mage::app()->getRequest()->getPost('input');
        $tipo = Mage::app()->getRequest()->getPost('tipo');
        if ($input == "cartao") {
            Mage::getSingleton('core/session')->setCardNumber($pid);
            Mage::getSingleton('core/session')->setCardType($tipo);
            Mage::getSingleton('core/session')->setBoleto("");
        } elseif ($input == "cvv") {
            Mage::getSingleton('core/session')->setCardCvv($pid);
        } elseif ($input == "cpf") {
            Mage::getSingleton('core/session')->setCPF($pid);
        } elseif ($input == "parcelas") {
            Mage::getSingleton('core/session')->setParcelas($pid);
        } elseif ($input == "name") {
            Mage::getSingleton('core/session')->setName($pid);
        } elseif ($input == "mes") {
            Mage::getSingleton('core/session')->setMes($pid);
        } elseif ($input == "ano") {
            Mage::getSingleton('core/session')->setAno($pid);
        } elseif ($input == "ddd") {
            Mage::getSingleton('core/session')->setDDD($pid);
        } elseif ($input == "telefone") {
            Mage::getSingleton('core/session')->setTelefone($pid);
        } elseif ($input == "boleto") {
            Mage::getSingleton('core/session')->setBoleto($tipo);
            Mage::getSingleton('core/session')->setCardNumber("");
            Mage::getSingleton('core/session')->setCardType("");
        }
    }

    public function successAction()
    {

        $order = new Mage_Sales_Model_Order();
        $lastOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order->loadByIncrementId($lastOrderId);
        $quoteId = $order->getQuoteId();
        $quote = Mage::getModel("sales/quote")->load($quoteId);

        try
        {
            $order->setTransactionIdBcash($quote->getTransactionIdBcash())
                  ->setStatusBcash($quote->getStatusBcash())
                  ->setDescriptionStatusBcash($quote->getDescriptionStatusBcash())
                  ->setPaymentLinkBcash($quote->getPaymentLinkBcash())
                  ->setPaymentMethodBcash($quote->getPaymentMethodBcash())
                  ->setInstallmentsBcash($quote->getInstallmentsBcash());
            $order->save();

            $order->sendNewOrderEmail();
        }
        catch (Exception $ex)
        {

        }

        $type = null;
        $payment_method_bcash = $order->getPaymentMethodBcash();
        if($payment_method_bcash) {
            $helper = new Bcash_Pagamento_Helper_PaymentMethod();
            $type = $helper->getPaymentMethod($payment_method_bcash);
        }

        $this->loadLayout();

        $this->getLayout()->getBlock('root')->setTemplate('page/2columns-right.phtml');

        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'link_pagamento_bcash',
            array('template' => 'pagamento/checkout/success.phtml')
        );

        $block->setOrder($order);
        $block->setQuote($quote);
        $block->setType($type);

        $this->getLayout()->getBlock('content')->append($block);

        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));

        $this->renderLayout();
    }
}
