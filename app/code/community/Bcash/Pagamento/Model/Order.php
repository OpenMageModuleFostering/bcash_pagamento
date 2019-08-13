<?php

require_once(Mage::getBaseDir("lib") . "/BcashApi/autoloader.php");

use Bcash_Pagamento_Helper_RegisterSdk;
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
    private $obj;
    private $sandbox;

    public function __construct()
    {
        $this->obj = Mage::getSingleton('Bcash_Pagamento_Model_PaymentMethod');
        $this->email = $this->obj->getConfigData('email');
        $this->token = $this->obj->getConfigData('token');
        $this->sandbox = $this->obj->getConfigData('sandbox');
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
            Mage::log($e->getErrors());

        } catch (ConnectionException $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . ' (Confirme se o serviço de cancelamento está habilitado para sua conta Bcash)');
            Mage::log($e->getErrors());
        }

        return $response;
    }

}