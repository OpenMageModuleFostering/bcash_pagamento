<?php

require_once(Mage::getBaseDir("lib") . "/Bcash/AutoLoader.php");
Bcash\AutoLoader::register();

use Bcash\Service\Installments;
use Bcash\Exception\ValidationException;
use Bcash\Exception\ConnectionException;
use Bcash\Domain\PaymentMethodEnum;

/**
 * Class Bcash_Pagamento_Block_Form_OnlineTransfer
 */
class Bcash_Pagamento_Block_Form_Onlinetransfer extends Mage_Payment_Block_Form
{
    /**
     * @var string
     */
    protected $_code = 'bcash_onlinetransfer';

    /**
     * @var
     */
    private $email;
    /**
     * @var
     */
    private $token;
    /**
     * @var
     */
    private $sandbox;
    /**
     * @var
     */
    private $max_installments;

    /**
     * @var
     */
    private $cards;
    /**
    * @var
    */
    private $boleto;
    /**
    * @var
    */
    private $tefs;
    /**
     * @var
     */
    private $desconto_credito_1x;
    /**
     * @var
     */
    private $quote;
    /**
     * @var
     */
    private $cpf;
    /**
     * @var
     */
    private $phone;

    /**
     * Instancia o template referente ao método de pagamento
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('bcash/pagamento/form/onlinetransfer.phtml');
        $this->email   = Mage::getStoreConfig('payment/bcash/email');
        $this->token   = Mage::getStoreConfig('payment/bcash/token');
        $this->sandbox = Mage::getStoreConfig('payment/bcash/sandbox');
        $this->max_installments = Mage::getStoreConfig('payment/bcash_creditcard/max_installments');
        $this->cpf = Mage::getStoreConfig('payment/bcash/cpf');
        $this->phone = Mage::getStoreConfig('payment/bcash/phone');
        $this->desconto_credito_1x = 0;
        $this->cards  = array(PaymentMethodEnum::VISA, PaymentMethodEnum::MASTERCARD, PaymentMethodEnum::AMERICAN_EXPRESS, PaymentMethodEnum::AURA, PaymentMethodEnum::DINERS, PaymentMethodEnum::HIPERCARD, PaymentMethodEnum::ELO);
        $this->boleto = PaymentMethodEnum::BANK_SLIP;
        $this->tefs   = array(PaymentMethodEnum::BB_ONLINE_TRANSFER, PaymentMethodEnum::BRADESCO_ONLINE_TRANSFER, PaymentMethodEnum::ITAU_ONLINE_TRANSFER, PaymentMethodEnum::BANRISUL_ONLINE_TRANSFER, PaymentMethodEnum::HSBC_ONLINE_TRANSFER);
    }

    public function getCpf()
    {
        return $this->cpf;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Retorna os meios de pagamento disponíveis no módulo Bcash.
     * @return mixed
     */
    public function getPaymentMethods()
    {
        //Mage::helper("bcash")->saveLog("Bcash_Pagamento_Block_Form_Onlinetransfer called getPaymentMethods OK");
        // Find allowed payment methods
        $listAllowed = $this->getAllowedPaymentMethods();

        return Mage::helper('bcash/paymentMethod')->getPaymentMethods($listAllowed, "onlinetransfer");
    }

    /**
     * Método para buscar métodos de pagamentos permitidos pela API
     *
     * @return array
     */
    private function getAllowedPaymentMethods()
    {
        $methods = array();
        $installments = new Installments($this->email, $this->token);
        try {
            $installments->enableSandBox($this->sandbox);
            // Any param value just for check
            $response = $installments->calculate(100.00, 1, false);
            // list methods
            foreach($response->paymentTypes as $types) {
                foreach($types->paymentMethods as $method) {
                    $methods[] = $method->id;
                }
            }
        } catch (ValidationException $e) {
            Mage::helper("bcash")->saveLog("ValidationException - Form_Onlinetransfer->getAllowedPaymentMethods: " . $e->getMessage(), $e->getErrors());
        } catch (ConnectionException $e) {
            Mage::helper("bcash")->saveLog("ConnectionException - Form_Onlinetransfer->getAllowedPaymentMethods: " . $e->getMessage(), $e->getErrors());
        }

        return $methods;
    }
}


