<?php

require_once(Mage::getBaseDir("lib") . "/Bcash/AutoLoader.php");
Bcash\AutoLoader::register();

use Bcash\Service\Cancellation;
use Bcash\Exception\ValidationException;
use Bcash\Exception\ConnectionException;

/**
 * Class Bcash_Pagamento_Model_Order
 */
class Bcash_Pagamento_Model_Order extends Mage_Core_Model_Abstract
{
    private $email;
    private $token;
    private $sandbox;

    public function __construct()
    {
        $this->email = Mage::getStoreConfig('payment/bcash/email');
        $this->token = Mage::getStoreConfig('payment/bcash/token');
        $this->sandbox = Mage::getStoreConfig('payment/bcash/sandbox');
    }

    /**
     * Método para cancelamento de transação de pagamento
     *
     * @param $transactionId
     * @return mixed
     */
    public function cancellation($transactionId)
    {
        // Instância de classe de cancelamento
        $cancellation = new Cancellation($this->email, $this->token);
        $cancellation->enableSandBox($this->sandbox);

        $response = null;
        try {
            if (!is_null($transactionId) && !empty($transactionId)) {
                // Envia cancelamento da transação para API
                $response = $cancellation->execute($transactionId);
            }
        } catch (ValidationException $e) {
            Mage::getSingleton('adminhtml/session')->addError('Erro: ' . $e->getMessage());
            Mage::helper("bcash")->saveLog("ValidationException - Model_Order->cancellation: " . $e->getMessage(), $e->getErrors());

        } catch (ConnectionException $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . ' (Confirme se o serviço de cancelamento está habilitado para sua conta Bcash)');
            Mage::helper("bcash")->saveLog("ConnectionException - Model_Order->cancellation: " . $e->getMessage(), $e->getErrors());
        }

        return $response;
    }

    public function getBcashInfoPayment($order_id){
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $quoteId = $order->getQuoteId();
        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);

        $info_payments = array(
            array("titulo" => "method_payment", "valor" => $quote->getPaymentMethodBcash()),
            array("titulo" => "Id transação Bcash", "valor" => $quote->getTransactionIdBcash()),
            array("titulo" => "Cod. do Status", "valor" => $quote->getStatusBcash()),
            array("titulo" => "Descrição do Status", "valor" =>  $quote->getDescriptionStatusBcash()),
            array("titulo" => "Link", "valor" => $quote->getPaymentLinkBcash()),
            array("titulo" => "Parcelas", "valor" => $quote->getInstallmentsBcash() . "x")
        );

        return $info_payments;
    }

}