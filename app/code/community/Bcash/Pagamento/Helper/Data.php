<?php

require_once(Mage::getBaseDir("lib") . "/Bcash/AutoLoader.php");
Bcash\AutoLoader::register();

use Bcash\Service\Consultation;
use Bcash\Exception\ValidationException;
use Bcash\Exception\ConnectionException;
use Bcash\Service\Installments;

class Bcash_Pagamento_Helper_Data extends Mage_Payment_Helper_Data
{

    private $email;
    private $token;
    private $sandbox;
    private $quote;
    private $max_installments;

    public function __construct()
    {
        $this->email = Mage::getStoreConfig('payment/bcash/email');
        $this->token = Mage::getStoreConfig('payment/bcash/token');
        $this->sandbox = Mage::getStoreConfig('payment/bcash/sandbox');
        $this->max_installments = Mage::getStoreConfig('payment/bcash_creditcard/max_installments');
        $this->desconto_credito_1x = 0;
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
            Mage::helper("bcash")->saveLog($e->getMessage(), $e->getErrors());

        } catch (ConnectionException $e) {
            Mage::getSingleton('adminhtml/session')->addError('Error:' . $e->getMessage());
            Mage::helper("bcash")->saveLog($e->getMessage(), $e->getErrors());
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
            Mage::helper("bcash")->saveLog("ValidationException - Helper_Data->getInstallments:" . $e->getMessage(), $e->getErrors());
            return array("ok" => false, "installments" => array("1" => $grandTotal));
        } catch (ConnectionException $e) {
            Mage::helper("bcash")->saveLog("ConnectionException - Helper_Data->getInstallments:" . $e->getMessage(), $e->getErrors());
            return array("ok" => false, "installments" => array("1" => $grandTotal));
        }
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


    /**
     * Método para registrar logs
     *
     * @param $text
     * @param null $array
     */
    public function saveLog($text, $array = null){
        if(!is_null($array)) {
            $text .= " - Detalhes: " . json_encode($array);
        }

        $logAtivo = Mage::getStoreConfig('payment/bcash/logfile');

        if($logAtivo) {
            $urlCurrent = Mage::helper('core/url')->getCurrentUrl();
            //Mage::log("Local loja: " . $urlCurrent, null, "bcash-magento.log");
            Mage::log($text, null, "bcash-magento.log");
        }
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