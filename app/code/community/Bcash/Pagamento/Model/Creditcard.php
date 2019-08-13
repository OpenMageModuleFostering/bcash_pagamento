<?php

/**
 * Class Bcash_Pagamento_Model_Creditcard
 */
class Bcash_Pagamento_Model_Creditcard extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code = 'bcash_creditcard';

    /**
     * @var string
     */
    protected $_formBlockType = 'bcash/form_creditcard';
    protected $_infoBlockType = 'bcash/info_creditcard';

    /**
     * Flag executa o método initalize() com o checkout completo.
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping  = false;

    protected $transaction;

    /**
     * Inicializa o método de pagamento. Chamado quando a compra é completa.
     * Objeto Order será criado após a chamada deste método.
     *
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     * @return Mage_Payment_Model_Abstract
     * @throws Mage_Payment_Model_Info_Exception
     */
    public function initialize($paymentAction, $stateObject)
    {
        //Mage::helper("bcash")->saveLog('Called ' . __METHOD__ . ' with payment ' . $paymentAction);
        //Mage::helper("bcash")->saveLog('Payment Creditcard visitor: ' . Mage::helper('core/http')->getRemoteAddr());
        parent::initialize($paymentAction, $stateObject);

        if ($paymentAction != 'sale') {
            return $this;
        }

        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(false);

        try {
            $result = $this->_customBeginPayment();
            $response = $result['response'];
            $payment_method = $result['payment_method'];
            $installments = $result['installments'];

            /*
            1 – Em andamento
            3 – Aprovada
            4 – Concluída
            5 – Disputa
            6 – Devolvida
            7 – Cancelada
            8 – Chargeback
            */

            // Salvar o PEDIDO em caso de SUCESSO e adicionar os dados da Transação
            if ($response->status != 1 && $response->status != 2) {

                $setIsNotified = false;
                switch($response->status)
                {
                    case 3: //3 – Aprovada
                    case 4: //4 – Concluída
                        $state = Mage_Sales_Model_Order::STATE_PROCESSING;
                        $setIsNotified = true;
                        break;
                    case 6://6 – Devolvida
                        $state = Mage_Sales_Model_Order::STATE_HOLDED;
                        break;
                    case 7://7 – Cancelada
                    case 8://8 – Chargeback
                        $state = Mage_Sales_Model_Order::STATE_CANCELED;
                        break;
                    default:
                        $state = null;
                        break;
                }

                if(!is_null($state)){
                    $stateObject->setState($state);
                    $stateObject->setStatus($state);
                    $stateObject->setIsNotified($setIsNotified);
                }
            }

            Mage::getSingleton('core/session')->setTransactionIdBcash($response->transactionId);
            Mage::getSingleton('core/session')->setStatusBcash($response->status);
            Mage::getSingleton('core/session')->setDescriptionStatusBcash(urldecode($response->descriptionStatus));
            Mage::getSingleton('core/session')->setPaymentLinkBcash(isset($response->paymentLink) ? urldecode($response->paymentLink) : null);
            Mage::getSingleton('core/session')->setPaymentMethodBcash($payment_method);
            Mage::getSingleton('core/session')->setInstallmentsBcash($installments);

            $cart = Mage::getSingleton('checkout/cart')->getQuote();
            $cart->setTransactionIdBcash($response->transactionId)
                 ->setStatusBcash($response->status)
                 ->setDescriptionStatusBcash(urldecode($response->descriptionStatus))
                 ->setPaymentLinkBcash(isset($response->paymentLink) ? urldecode($response->paymentLink) : null)
                 ->setPaymentMethodBcash($payment_method)
                 ->setInstallmentsBcash($installments);
            $cart->save();

        } catch (Exception $e) {
            Mage::helper("bcash")->saveLog($e->getMessage());
            throw new Mage_Payment_Model_Info_Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Inicializa a transação atual via SDK Api Bcash.
     * Respond with token
     * @throws SoapFault
     * @throws Mage_Exception
     * @throws Exception
     */
    protected function _customBeginPayment()
    {
        $this->transaction = new Bcash_Pagamento_Helper_Transaction();
        return $this->transaction->startTransaction();
    }

    /**
     * Assign data to info model instance
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        //Mage::helper("bcash")->saveLog('Creditcard :: Assign Data with Bcash');
        $result = parent::assignData($data);
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        $params['installments_bcash'] = isset($params['installments_bcash']) ? $params['installments_bcash'] : 1;

        return $result;
    }
}
