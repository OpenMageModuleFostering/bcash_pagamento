<?php

/**
 * Class Bcash_Pagamento_Block_Info_Creditcard
 */
class Bcash_Pagamento_Block_Info_Creditcard extends Mage_Payment_Block_Info
{
    protected function _construct(){
        parent::_construct();
        $this->setTemplate('bcash/pagamento/info.phtml');
    }

    public function getInfoPayment(){

        $order_id = $this->getInfo()->getOrder()->getIncrementId();
        $info_payments = Mage::getModel('bcash/order')->getBcashInfoPayment($order_id);

        return $info_payments;
    }

	/**
	 * @param null $transport
	 * @return Varien_Object
     */
	protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $data = array();
        if ($this->getInfo()->getCustomFieldOne()) {
            $data[Mage::helper('payment')->__('Custom Field One')] = $this->getInfo()->getCustomFieldOne();
        }

        if ($this->getInfo()->getCustomFieldTwo()) {
            $data[Mage::helper('payment')->__('Custom Field Two')] = $this->getInfo()->getCustomFieldTwo();
        }

        $transport = parent::_prepareSpecificInformation($transport);

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
