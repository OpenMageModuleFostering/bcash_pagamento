<?php


/**
 * Class Bcash_Pagamento_Model_PaymentMethod
 */
class Bcash_Pagamento_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code = 'pagamento';

    /**
     * @var string
     */
    protected $_formBlockType = 'pagamento/form_payment';

    /** Flag executa o método initalize() com o checkout completo.
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canUseCheckout = true;
    //Disable multi-shipping for this payment module.
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
        Mage::log('Called ' . __METHOD__ . ' with payment ' . $paymentAction);
        Mage::log('Payment visitor: ' . Mage::helper('core/http')->getRemoteAddr());
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
            //Mage::log(print_r($result, true));
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
            Mage::log($e->getMessage());
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
        Mage::log('Assign Data with Bcash');
        $result = parent::assignData($data);
        $params = Mage::app()->getFrontController()->getRequest()->getParams();
        $params['installments_bcash'] = isset($params['installments_bcash']) ?$params['installments_bcash']:1;

        //Adiciona Desconto ao Pedido caso 1x Credito, Boleto ou TEF (configurados no Backend)
        if(isset($params['payment-method'])) {
            $discount = 0;
            if ($params['installments_bcash'] == 1) {
                $discount = $this->calculateDiscount($params['payment-method']);
            }
            if(!empty($params['payment-method'])) {
                $this->addDiscountToQuote($discount);
            }
        }

        return $result;
    }

    public function calculateDiscount($payment_method)
    {
        $transaction = new Bcash_Pagamento_Helper_Transaction();
        return $transaction->calculateDiscount($payment_method);
    }

    public function addDiscountToQuote($discountAmount = 0)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $objShippingAddress = $cart->getQuote()->getShippingAddress();


        //Mage::log($objShippingAddress);

        if($discountAmount > 0) {
            // Update quote
            Mage::dispatchEvent(
                'sales_quote_payment_import_data_before',
                array(
                    'quote' => $cart->getQuote()
                )
            );
            $discountDescription = $objShippingAddress->getDiscountDescription();
            if(!empty($discountDescription)) { $discountDescription .= " + "; }

            $objShippingAddress->setDiscountDescription($discountDescription . 'Meio de pagamento');


            $grandTotal = $objShippingAddress->getGrandTotal();
            $subTotalWithDiscount = $objShippingAddress->getSubtotalWithDiscount();
            $baseGrandTotal = $objShippingAddress->getBaseGrandTotal();
            $baseSubTotalWithDiscount = $objShippingAddress->getBaseSubtotalWithDiscount();

            // Outros descontos aplicados
            $objDiscountAmount = $objShippingAddress->getDiscountAmount();
            if ($objDiscountAmount <> 0) {
                $discountAmount = (-1 * ((-$discountAmount) + $objDiscountAmount));
                $grandTotal = $grandTotal + (-1 * $objDiscountAmount);
                $subTotalWithDiscount = $subTotalWithDiscount + (-1 * $objDiscountAmount);
                $baseGrandTotal = $baseGrandTotal + (-1 * $objDiscountAmount);
                $baseSubTotalWithDiscount = $baseSubTotalWithDiscount + (-1 * $objDiscountAmount);
            }

            $objShippingAddress->addTotal(array(
                'code' => 'discount',
                'title' => "Desconto",
                'value' => -$discountAmount,
            ));

            $totalDiscountAmount = $discountAmount;
            $subtotalWithDiscount = $subTotalWithDiscount - $discountAmount;
            $baseTotalDiscountAmount = $discountAmount;
            $baseSubtotalWithDiscount = $baseSubTotalWithDiscount - $discountAmount;

            $objShippingAddress->setDiscountAmount(-$totalDiscountAmount);
            $objShippingAddress->setSubtotalWithDiscount($subtotalWithDiscount);
            $objShippingAddress->setBaseDiscountAmount($baseTotalDiscountAmount);
            $objShippingAddress->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
            $objShippingAddress->setGrandTotal($grandTotal - $totalDiscountAmount);
            $objShippingAddress->setBaseGrandTotal($baseGrandTotal - $baseTotalDiscountAmount);
            $objShippingAddress->save();
        }
    }
}
