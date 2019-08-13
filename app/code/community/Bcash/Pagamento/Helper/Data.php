<?php

require_once(Mage::getBaseDir("lib") . "/BcashApi/autoloader.php");

use Bcash\Service\Consultation;
use Bcash\Exception\ValidationException;
use Bcash\Exception\ConnectionException;

class Bcash_Pagamento_Helper_Data extends Mage_Payment_Helper_Data
{

    private $email;
    private $token;
    private $obj;
    private $sandbox;

    public function __construct()
    {
        $this->obj = Mage::getSingleton('Bcash_Pagamento_Model_PaymentMethod');
        $this->email = $this->obj->getConfigData('email');
        $this->token = $this->obj->getConfigData('token');
        $this->sandbox = $this->obj->getConfigData('sandbox');
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

    public function setTransaction()
    {
        //Create Transaction with Bcash

        die('setTransaction');

    }
}