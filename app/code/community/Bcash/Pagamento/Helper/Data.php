<?php

require_once(Mage::getBaseDir("lib") . "/BcashApi/autoloader.php");

use Bcash\Service\Consultation;
use Bcash\Exception\ValidationException;
use Bcash\Exception\ConnectionException;
use Bcash\Service\Installments;

class Bcash_Pagamento_Helper_Data extends Mage_Payment_Helper_Data
{

    private $email;
    private $token;
    private $obj;
    private $sandbox;
    private $quote;
    private $max_installments;

    public function __construct()
    {
        $this->obj = Mage::getSingleton('Bcash_Pagamento_Model_PaymentMethod');
        $this->email = $this->obj->getConfigData('email');
        $this->token = $this->obj->getConfigData('token');
        $this->sandbox = $this->obj->getConfigData('sandbox');
        $this->max_installments = $this->obj->getConfigData('max_installments');
        $this->desconto_credito_1x = $this->obj->getConfigData('desconto_credito_1x');
    }

    public function getTransaction($transactionId = null, $orderId = null)
    {
        $response = null;
        $consultation = new Consultation($this->email, $this->token);
        $consultation->enableSandBox($this->sandbox);

        try {
            if (!empty($transactionId) && !is_null($transactionId)) {
                //Consulta pelo id da transação
                $response = $consultation->searchByTransaction($transactionId);
            } else if (!empty($orderId) && !is_null($orderId)) {
                //Consulta pelo id do pedido
                $response = $consultation->searchByOrder($orderId);
            }

        } catch (ValidationException $e) {
            Mage::getSingleton('adminhtml/session')->addError('Error:' . $e->getMessage());
            Mage::log($e->getMessage() . " :: " . $e->getErrors());

        } catch (ConnectionException $e) {
            Mage::getSingleton('adminhtml/session')->addError('Error:' . $e->getMessage());
            Mage::log($e->getMessage() . " :: " . $e->getErrors());
        }

        return $response;
    }

    /**
     * Retorna os parcelamentos possíveis via cartão de crédito.
     * @return array
     */
    public function getInstallments()
    {
        $installments = new Installments($this->email, $this->token);
        try {
            $sessionCheckout = Mage::getSingleton('checkout/session');
            $quoteId = $sessionCheckout->getQuoteId();
            $this->quote = Mage::getModel("sales/quote")->load($quoteId);
            $grandTotal = floatval(number_format($this->quote->getData('grand_total'), 2, '.', ''));
            $ignoreScheduledDiscount = false;
            if($this->sandbox){
                $installments->enableSandBox(true);
            }
            $response = $installments->calculate($grandTotal, $this->max_installments, $ignoreScheduledDiscount);
            return array("ok" => true, "installments" => array(0 => $this->prepareInstallmentsCards($response)));
        } catch (ValidationException $e) {
            Mage::log("Erro Bcash ValidationException:" . $e->getMessage());
            Mage::log($e->getErrors());
            return array("ok" => false, "installments" => array("1" => $grandTotal));
        } catch (ConnectionException $e) {
            Mage::log("Erro Bcash ConnectionException:" . $e->getMessage());
            Mage::log($e->getErrors());
            return array("ok" => false, "installments" => array("1" => $grandTotal));
        }
    }

    public function setTransaction()
    {
        //Create Transaction with Bcash

        die('setTransaction');

    }

    /**
     * Sincronização de informações da transação Bcash entre Quote e Order
     *
     * @param $orderId
     * @param $quoteId
     * @throws Exception
     */
    public function updateOrderSyncBcashDataWithQuote($orderId, $quoteId)
    {
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $order = Mage::getModel('sales/order')->load($orderId);

        $order->setTransactionIdBcash($quote->getTransactionIdBcash())
              ->setStatusBcash($quote->getStatusBcash())
              ->setDescriptionStatusBcash($quote->getDescriptionStatusBcash())
              ->setPaymentLinkBcash($quote->getPaymentLinkBcash())
              ->setPaymentMethodBcash($quote->getPaymentMethodBcash())
              ->setInstallmentsBcash($quote->getInstallmentsBcash());
        $order->save();
    }

    private function prepareInstallmentsCards($installments)
    {
        foreach ($installments->paymentTypes as $obj) {
            if ('card' != $obj->name) {
                unset($obj);
            } else {
                if ($this->desconto_credito_1x) {
                    $subTotal = floatval($this->quote->getSubtotal());
                    $desconto = ($this->desconto_credito_1x / 100) * $subTotal;
                    if ($desconto) {
                        foreach ($obj->paymentMethods as $type) {
                            foreach ($type->installments as &$installment) {
                                if ($installment->number == 1) {
                                    $installment->installmentAmount -= $desconto;
                                    $installment->installmentAmountDesc = " ({$this->desconto_credito_1x} % de desconto)";
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $installments;
    }
}