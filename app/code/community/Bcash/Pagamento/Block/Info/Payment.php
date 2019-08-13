<?php

/**
 * Class Bcash_Pagamento_Block_Info_Payment
 */
class Bcash_Pagamento_Block_Info_Payment extends Mage_Payment_Block_Info
{

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
