<?php

require_once(Mage::getBaseDir("lib") . "/BcashApi/autoloader.php");

use Bcash\Domain\NotificationStatusEnum;

/**
 * Controller Bcash_Pagamento_Admin_Sales_OrderController
 */
class Bcash_Pagamento_Admin_Sales_OrderController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Recebe requisição de cancelamento de um pedido
     */
    public function canceltransactionAction()
    {
        $orderId = trim(stripslashes($this->getRequest()->getParam('order_id')));

        if ($orderId) {
            // Cancelamento da transação do pedido
            if ($this->cancelBcashTransaction($orderId)) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Pedido cancelado com sucesso.');
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Não foi possível cancelar o pedido.');
            }

            // Retorna para tela do pedido
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
        }
    }

    /**
     * Recebe requisição de cancelamento de uma lista de pedidos
     */
    public function masscanceltransactionAction()
    {
        $ordersIds = $this->getRequest()->getParam('order_ids');

        if (!is_array($ordersIds)) {
            Mage::getSingleton('adminhtml/session')->addError('Selecione pedidos para cancelamento.');
        } else {
            try {
                // Counters
                $cancelSuccess = 0;
                $cancelFail = 0;

                foreach ($ordersIds as $orderId) {
                    // Cancelamento da transação do pedido
                    if ($this->cancelBcashTransaction($orderId)) {
                        $cancelSuccess++;
                    } else {
                        $cancelFail++;
                    }
                }

                if ($cancelSuccess > 0) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total de %d pedido(s) cancelados com sucesso.', $cancelSuccess
                        )
                    );
                }
                if ($cancelFail > 0) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('adminhtml')->__(
                            'Total de %d falha(s) no cancelamento.', $cancelFail
                        )
                    );
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        // Retorna para listagem de pedidos
        $this->_redirect('adminhtml/sales_order');
    }

    /**
     * Método para cancelamento da transação de pagamento do pedido
     *
     * @param $orderId
     * @return bool
     */
    private function cancelBcashTransaction($orderId)
    {
        // Carrega pedido a partir de Id de entidade
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($order->getId()) {
            try {
                // Consulta transação Bcash
                $quoteId = $order->getQuoteId();
                $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
                $orderTransactionBcash = $quote->getTransactionIdBcash();
                $transactionInfo = Mage::helper('pagamento')->getTransaction($orderTransactionBcash);

                // Checa se o status da transaçao Bcash permite cancelamento
                if($transactionInfo->transacao->cod_status == NotificationStatusEnum::IN_PROGRESS || $transactionInfo->transacao->cod_status == NotificationStatusEnum::APPROVED) {
                    $pagamentoOrderModel = Mage::getModel('pagamento/order');
                    $responseCancellation = $pagamentoOrderModel->cancellation($orderTransactionBcash);

                    if ($responseCancellation != null) {
                        if ($responseCancellation->transactionStatusId == NotificationStatusEnum::CANCELLED) {
                            // Registro do cancelamento no pedido
                            $order->registerCancellation('Cancelamento efetivado no Bcash. ', TRUE)->save();

                            // Atualiza status na transação
                            $quote->setStatusBcash($responseCancellation->transactionStatusId)
                                  ->setDescriptionStatusBcash($responseCancellation->transactionStatusDescription);
                            $quote->save();

                            return true;
                        } else if ($responseCancellation->transactionStatusId == NotificationStatusEnum::REFUNDED) {
                            // Registro do estorno do pagamento
                            $payment = $order->getPayment();
                            $payment->setTransactionId($orderTransactionBcash)
                                ->setPreparedMessage("Pagamento devolvido.")
                                ->setIsTransactionClosed(1);
                            $payment->setRefundTransactionId($orderTransactionBcash);
                            $order->registerCancellation('Pagamento devolvido no Bcash. ', TRUE)->save();

                            // Atualiza status na transação
                            $quote->setStatusBcash($responseCancellation->transactionStatusId)
                                ->setDescriptionStatusBcash($responseCancellation->transactionStatusDescription);
                            $quote->save();

                            return true;
                        } else {
                            // Registro em histórico do pedido: cancelamento não efetivado
                            $order->addStatusHistoryComment('Tentativa de cancelamento não efetivada no Bcash.');
                            $order->save();

                            return false;
                        }
                    }
                }

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                Mage::log("Cancellation error: " . $e->getMessage());
            }
        }

        return false;
    }
}