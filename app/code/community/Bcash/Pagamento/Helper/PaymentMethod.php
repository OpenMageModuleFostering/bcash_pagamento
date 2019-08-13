<?php

class Bcash_Pagamento_Helper_PaymentMethod
{
    
    const CARD_TYPE = "CARD";
    const BANKSLIP_TYPE = "BANKSLIP";
    const ONLINE_TRANSFER_TYPE = "ONLINE_TRANSFER";
    
    private static $cards;
    private static $onlineTransfer;
    private static $bankSlip;
    
    public function __construct()
    {
        self::$cards = array();
        self::$bankSlip = array();
        self::$onlineTransfer = array();
    }

    private function createPayment($id, $title, $type, $minimunValue, $maxInstallments)
    {
        $payment = new stdClass();
        $payment->id = $id;
        $payment->title = $title;
        $payment->type = $type;
        $payment->minimunValue = $minimunValue;
        $payment->maxInstallments = $maxInstallments;
        return $payment;
    }

    public function getPaymentMethods($allowedMethods = null)
    {
        if(!is_null($allowedMethods)) {
            $this->createAllowedPaymentMethods($allowedMethods);
        }
        else {
            $this->createAllPaymentMethods();
        }

        return array(
            self::CARD_TYPE => self::$cards,
            self::BANKSLIP_TYPE => self::$bankSlip,
            self::ONLINE_TRANSFER_TYPE => self::$onlineTransfer
        );
    }

    public function getPaymentMethod($payment_method){
        $methods = $this->getPaymentMethods();
        foreach ($methods as $type => $methodsType) {
            foreach ($methodsType as $PaymentObject) {
                if($payment_method == $PaymentObject->id){
                    return $PaymentObject;
                }
            }
        }
        return null;
    }

    private function createAllowedPaymentMethods($listAllowed) {
        // Card: Visa
        if(in_array(1, $listAllowed)) {
            self::$cards[] = $this->createPayment(1,  'Visa', self::CARD_TYPE, 1.0, 12);
        }
        // Card: Master
        if(in_array(2, $listAllowed)) {
            self::$cards[] = $this->createPayment(2,  'Master', self::CARD_TYPE, 1.0, 12);
        }
        // Card: American Express
        if(in_array(37, $listAllowed)) {
            self::$cards[] = $this->createPayment(37, 'American Express', self::CARD_TYPE, 1.0, 12);
        }
        // Card: Aura
        if(in_array(45, $listAllowed)) {
            self::$cards[] = $this->createPayment(45, 'Aura', self::CARD_TYPE, 1.0, 24);
        }
        // Card: Diners
        if(in_array(55, $listAllowed)) {
            self::$cards[] = $this->createPayment(55, 'Diners', self::CARD_TYPE, 1.0, 12);
        }
        // Card: Hipercard
        if(in_array(56, $listAllowed)) {
            self::$cards[] = $this->createPayment(56, 'HiperCard', self::CARD_TYPE, 1.0, 12);
        }
        // Card: Elo
        if(in_array(63, $listAllowed)) {
            self::$cards[] = $this->createPayment(63, 'Elo', self::CARD_TYPE, 1.0, 12);
        }

        // Boleto
        if(in_array(10, $listAllowed)) {
            self::$bankSlip[] = $this->createPayment(10, 'Boleto Bancário', self::BANKSLIP_TYPE, 0.01, 1);
        }

        // OnlineTransfer : BB
        if(in_array(58, $listAllowed)) {
            self::$onlineTransfer[] = $this->createPayment(58, 'BB', self::ONLINE_TRANSFER_TYPE, 0.01, 1);
        }
        // OnlineTransfer : Bradesco
        if(in_array(59, $listAllowed)) {
            self::$onlineTransfer[] = $this->createPayment(59, 'Bradesco', self::ONLINE_TRANSFER_TYPE, 0.01, 1);
        }
        // OnlineTransfer : Itaú
        if(in_array(60, $listAllowed)) {
            self::$onlineTransfer[] = $this->createPayment(60, 'Itaú', self::ONLINE_TRANSFER_TYPE, 0.01, 1);
        }
        // OnlineTransfer : Banrisul
        if(in_array(61, $listAllowed)) {
            self::$onlineTransfer[] = $this->createPayment(61, 'Banrisul', self::ONLINE_TRANSFER_TYPE, 0.01, 1);
        }
        // OnlineTransfer : HSBC
        if(in_array(62, $listAllowed)) {
            self::$onlineTransfer[] = $this->createPayment(62, 'HSBC', self::ONLINE_TRANSFER_TYPE, 0.01, 1);
        }
    }

    private function createAllPaymentMethods() {
        self::$cards = array(
            $this->createPayment(1,  'Visa', self::CARD_TYPE, 1.0, 12),
            $this->createPayment(2,  'Master', self::CARD_TYPE, 1.0, 12),
            $this->createPayment(37, 'American Express', self::CARD_TYPE, 1.0, 12),
            $this->createPayment(45, 'Aura', self::CARD_TYPE, 1.0, 24),
            $this->createPayment(55, 'Diners', self::CARD_TYPE, 1.0, 12),
            $this->createPayment(56, 'HiperCard', self::CARD_TYPE, 1.0, 12),
            $this->createPayment(63, 'Elo', self::CARD_TYPE, 1.0, 12)
        );

        self::$bankSlip = array(
            $this->createPayment(10, 'Boleto Bancário', self::BANKSLIP_TYPE, 0.01, 1)
        );

        self::$onlineTransfer = array(
            $this->createPayment(58, 'BB', self::ONLINE_TRANSFER_TYPE, 0.01, 1),
            $this->createPayment(59, 'Bradesco', self::ONLINE_TRANSFER_TYPE, 0.01, 1),
            $this->createPayment(60, 'Itaú', self::ONLINE_TRANSFER_TYPE, 0.01, 1),
            $this->createPayment(61, 'Banrisul', self::ONLINE_TRANSFER_TYPE, 0.01, 1),
            $this->createPayment(62, 'HSBC', self::ONLINE_TRANSFER_TYPE, 0.01, 1)
        );
    }
}
